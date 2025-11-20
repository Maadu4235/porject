const express = require('express');
const axios = require('axios');
const router = express.Router();

// Proxy to Indeed RapidAPI (indeed11.p.rapidapi.com)
router.post('/search', async (req, res) => {
  try {
    const { query = 'developer', city = '' } = req.body;

    const apiKey = process.env.INDEED_API_KEY;
    if (!apiKey) {
      return res.status(500).json({ success: false, message: 'INDEED_API_KEY not configured on server' });
    }

    const url = 'https://indeed11.p.rapidapi.com/';
    const headers = {
      'X-RapidAPI-Key': apiKey,
      'X-RapidAPI-Host': 'indeed11.p.rapidapi.com',
      'Content-Type': 'application/json'
    };

    const params = {
      query,
      location: city || undefined,
      page_id: 1
    };

    // POST to RapidAPI Indeed endpoint
    const response = await axios.post(url, params, { headers, timeout: 15000 });

    // Normalize common RapidAPI Indeed response shapes into a simple jobs array
    const respData = response.data || {};

    function extractJobs(data) {
      // Try known shapes
      let items = [];
      if (Array.isArray(data)) items = data;
      else if (Array.isArray(data.results)) items = data.results;
      else if (Array.isArray(data.jobs)) items = data.jobs;
      else if (Array.isArray(data.data)) items = data.data;
      else if (data.response && Array.isArray(data.response.results)) items = data.response.results;

      // Map to a simplified job object
      return items.map(item => {
        return {
          title: item.title || item.jobTitle || item.position || '',
          company: item.company || item.companyName || item.employer || '',
          location: item.location || item.formattedLocation || item.address || city || '',
          salary: item.salary || item.compensation || item.salarySnippet || '',
          snippet: item.snippet || item.summary || item.description || '',
          link: item.url || item.jobUrl || item.link || '',
          contact: item.contact || ''
        };
      });
    }

    const jobs = extractJobs(respData);

    res.json({ success: true, raw: respData, jobs });
  } catch (err) {
    console.error('Indeed proxy error:', err.message || err);
    // If the RapidAPI call fails, return a helpful message
    res.status(500).json({ success: false, message: 'Indeed API request failed', error: err.message || String(err) });
  }
});

module.exports = router;
