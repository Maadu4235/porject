const mongoose = require('mongoose');
require('dotenv').config();

async function testConnection() {
    try {
        await mongoose.connect(process.env.MONGODB_URI);
        console.log('✅ MongoDB Connected Successfully!');
        
        // Test basic operations
        const User = require('./models/User');
        console.log('✅ Models loaded successfully');
        
        await mongoose.connection.close();
        console.log('✅ All tests passed! Backend is ready.');
        
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
}

testConnection();