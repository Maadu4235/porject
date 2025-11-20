// Enhanced search listings function with OpenAI integration
async function searchListings() {
    const query = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const distance = document.getElementById('distanceFilter').value;
    
    if (!query.trim()) {
        alert('Please enter a search term');
        return;
    }

    // Show loading state
    const searchBtn = document.querySelector('.btn-primary.btn-lg');
    const originalText = searchBtn.innerHTML;
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    searchBtn.disabled = true;

    try {
        const results = await searchWithOpenAI(query, category, distance);
        displaySearchResults(results);
    } catch (error) {
        console.error('Search error:', error);
        alert('Search failed. Please try again.');
    } finally {
        // Restore button state
        searchBtn.innerHTML = originalText;
        searchBtn.disabled = false;
    }
}

// OpenAI search function
async function searchWithOpenAI(query, category, distance) {
    // Your OpenAI API Key - IMPORTANT: In production, use a backend service
    const OPENAI_API_KEY = 'your-openai-api-key-here';
    
    const client = new OpenAI({
        apiKey: OPENAI_API_KEY,
        dangerouslyAllowBrowser: true // Only for development!
    });

    // Construct the prompt based on search parameters
    let prompt = `As a local job and service discovery platform, provide 5 relevant opportunities for: "${query}"`;
    
    if (category) {
        const categoryMap = {
            'job': 'job opportunities',
            'service': 'local services',
            'skill': 'skill development programs'
        };
        prompt += ` in the category of ${categoryMap[category] || category}`;
    }
    
    if (distance) {
        prompt += ` within ${distance} km radius`;
    }
    
    prompt += `. For each opportunity, provide: title, company/provider, short description, estimated salary/price range, and location. Format as JSON array.`;

    try {
        const completion = await client.chat.completions.create({
            model: "gpt-3.5-turbo",
            messages: [
                {
                    role: "system",
                    content: "You are a helpful assistant for a local discovery platform called Dwar. Provide realistic job opportunities, services, and skill programs that would be available in typical Indian cities and neighborhoods. Return data in JSON format."
                },
                {
                    role: "user",
                    content: prompt
                }
            ],
            temperature: 0.7,
            max_tokens: 1000
        });

        const responseText = completion.choices[0].message.content;
        
        // Try to parse JSON response
        try {
            return JSON.parse(responseText);
        } catch (parseError) {
            // If JSON parsing fails, create structured data from text
            return formatTextResponse(responseText);
        }
    } catch (error) {
        console.error('OpenAI API error:', error);
        throw new Error('Failed to fetch search results');
    }
}

// Format text response into structured data
function formatTextResponse(text) {
    const lines = text.split('\n').filter(line => line.trim());
    const results = [];
    let currentItem = {};
    
    lines.forEach(line => {
        if (line.match(/^\d+\./) || line.includes('Title:')) {
            if (Object.keys(currentItem).length > 0) {
                results.push(currentItem);
            }
            currentItem = {
                title: line.replace(/^\d+\.\s*|Title:\s*/i, '').trim(),
                type: 'opportunity'
            };
        } else if (line.includes('Company:') || line.includes('Provider:')) {
            currentItem.company = line.replace(/Company:|Provider:/i, '').trim();
        } else if (line.includes('Description:')) {
            currentItem.description = line.replace(/Description:/i, '').trim();
        } else if (line.includes('Salary:') || line.includes('Price:')) {
            currentItem.salary = line.replace(/Salary:|Price:/i, '').trim();
        } else if (line.includes('Location:')) {
            currentItem.location = line.replace(/Location:/i, '').trim();
        }
    });
    
    if (Object.keys(currentItem).length > 0) {
        results.push(currentItem);
    }
    
    return results.length > 0 ? results : [{
        title: 'Manual Search Required',
        description: 'Please try refining your search terms or browse categories directly.',
        type: 'info'
    }];
}

// Display search results
function displaySearchResults(results) {
    // Create or update results container
    let resultsContainer = document.getElementById('searchResultsContainer');
    
    if (!resultsContainer) {
        resultsContainer = document.createElement('div');
        resultsContainer.id = 'searchResultsContainer';
        resultsContainer.className = 'container mt-5';
        
        // Insert after hero section
        const heroSection = document.querySelector('.hero-section');
        heroSection.parentNode.insertBefore(resultsContainer, heroSection.nextSibling);
    }
    
    if (!Array.isArray(results) || results.length === 0) {
        resultsContainer.innerHTML = `
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4>No results found</h4>
                        <p>Try different search terms or browse our categories.</p>
                    </div>
                </div>
            </div>
        `;
        return;
    }
    
    // Generate results HTML
    let resultsHTML = `
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4">Search Results</h3>
                <div class="row">
    `;
    
    results.forEach((item, index) => {
        const badgeColor = item.type === 'job' ? 'primary' : 
                          item.type === 'service' ? 'success' : 'warning';
        
        resultsHTML += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title">${item.title || 'Opportunity'}</h5>
                            <span class="badge bg-${badgeColor}">${item.type || 'opportunity'}</span>
                        </div>
                        ${item.company ? `<p class="card-text"><strong>Company:</strong> ${item.company}</p>` : ''}
                        ${item.description ? `<p class="card-text">${item.description}</p>` : ''}
                        ${item.salary ? `<p class="card-text"><strong>Compensation:</strong> ${item.salary}</p>` : ''}
                        ${item.location ? `<p class="card-text"><strong>Location:</strong> ${item.location}</p>` : ''}
                    </div>
                    <div class="card-footer bg-transparent">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewDetails(${index})">
                            View Details
                        </button>
                        <button class="btn btn-primary btn-sm ms-2" onclick="saveOpportunity(${index})">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    resultsHTML += `
                </div>
            </div>
        </div>
    `;
    
    resultsContainer.innerHTML = resultsHTML;
}

// View details function
function viewDetails(index) {
    alert(`Viewing details for opportunity ${index + 1}`);
    // You can implement a modal or redirect to detail page
}

// Save opportunity function
function saveOpportunity(index) {
    alert(`Saved opportunity ${index + 1} to your favorites`);
    // Implement save functionality
}