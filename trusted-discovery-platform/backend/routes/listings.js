const express = require('express');
const Listing = require('../models/Listing');
const OpenAI = require('openai');

// Initialize OpenAI client using OPENAI_API_KEY from env
const openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });

const router = express.Router();

// Get all listings
router.get('/', async (req, res) => {
  try {
    const listings = await Listing.find({ isActive: true })
      .sort({ createdAt: -1 })
      .limit(20);

    res.json({ 
      success: true, 
      listings 
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ 
      success: false, 
      message: 'Server error while fetching listings' 
    });
  }
});

// Create new listing (NO AUTH REQUIRED for hackathon)
router.post('/', async (req, res) => {
  try {
    const listingData = {
      ...req.body,
      // Add default values for required fields
      isActive: true,
      isVerified: false,
      rating: 0,
      reviewCount: 0
    };

    const listing = new Listing(listingData);
    await listing.save();
    
    res.status(201).json({ 
      success: true, 
      message: 'Listing created successfully', 
      listing 
    });
  } catch (error) {
    console.error('Listing creation error:', error);
    res.status(500).json({ 
      success: false, 
      message: 'Server error while creating listing',
      error: error.message 
    });
  }
});

// Get single listing
router.get('/:id', async (req, res) => {
  try {
    const listing = await Listing.findById(req.params.id);
    
    if (!listing) {
      return res.status(404).json({
        success: false,
        message: 'Listing not found'
      });
    }

    res.json({
      success: true,
      listing
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({
      success: false,
      message: 'Server error while fetching listing'
    });
  }
});

module.exports = router;

// AI-powered search: summarize available jobs in a given city
router.post('/search-ai', async (req, res) => {
  try {
    const { city, query } = req.body;
    if (!city) {
      return res.status(400).json({ success: false, message: 'City is required' });
    }

    // Find job listings where the address contains the city name (case-insensitive)
    let listings = [];
    try {
      listings = await Listing.find({
        'location.address': { $regex: city, $options: 'i' },
        type: 'job',
        isActive: true
      }).limit(40);
    } catch (dbErr) {
      console.warn('DB query failed, using fallback sample listings for search-ai. Error:', dbErr.message);
      // Fallback sample listings for local testing when DB is not available
      listings = [
        {
          title: 'Retail Sales Associate',
          description: 'Customer-facing retail job with flexible shifts.',
          type: 'job',
          category: 'Retail',
          location: { address: `${city} - Market Area`, coordinates: [0,0] },
          contactPhone: '+91 90000 00001',
          salary: { min: 8000, max: 12000, currency: 'INR' }
        },
        {
          title: 'Delivery Driver',
          description: 'Two-wheeler delivery service, part-time available.',
          type: 'job',
          category: 'Logistics',
          location: { address: `${city} - Logistics Hub`, coordinates: [0,0] },
          contactPhone: '+91 90000 00002',
          salary: { min: 10000, max: 15000, currency: 'INR' }
        }
      ];
    }

    if (!listings || listings.length === 0) {
      return res.json({ success: true, ai: 'No jobs found for this city.', listings: [] });
    }

    // Prepare a concise text representation of the listings for the AI prompt
    const listingText = listings.map((l, idx) => {
      return `${idx + 1}. Title: ${l.title}\n   Description: ${l.description.substring(0, 200).replace(/\n/g, ' ')}${l.description.length>200? '...':''}\n   Salary: ${l.salary && (l.salary.min || l.salary.max) ? `${l.salary.min||''}-${l.salary.max||''} ${l.salary.currency||'INR'}` : 'Not specified'}\n   Address: ${l.location?.address || 'N/A'}\n   Contact: ${l.contactPhone || 'N/A'}`;
    }).join('\n\n');

    const systemPrompt = `You are a helpful assistant that summarizes local job listings for users. Provide a short, easy-to-read summary of available jobs, highlight a top 5 list (title, 1-line summary, salary, address/contact), and include any quick tips for applying. Keep the answer short and user-friendly.`;

    const userPrompt = `City: ${city}\nUser Query: ${query || 'Which jobs are available?'}\n\nListings:\n${listingText}`;

    // Call OpenAI Chat Completion
    const completion = await openai.chat.completions.create({
      model: 'gpt-3.5-turbo',
      messages: [
        { role: 'system', content: systemPrompt },
        { role: 'user', content: userPrompt }
      ],
      max_tokens: 450,
      temperature: 0.2
    });

    const aiText = completion?.choices?.[0]?.message?.content || 'Unable to generate summary.';

    res.json({ success: true, ai: aiText, listings });
  } catch (error) {
    console.error('AI search error:', error);
    res.status(500).json({ success: false, message: 'Server error during AI search', error: error.message });
  }
});