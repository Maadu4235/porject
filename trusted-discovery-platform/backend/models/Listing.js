const mongoose = require('mongoose');

const listingSchema = new mongoose.Schema({
  title: {
    type: String,
    required: true,
    trim: true
  },
  description: {
    type: String,
    required: true
  },
  type: {
    type: String,
    enum: ['job', 'service', 'skill'],
    required: true
  },
  category: {
    type: String,
    required: true
  },
  location: {
    type: {
      type: String,
      enum: ['Point'],
      default: 'Point'
    },
    coordinates: {
      type: [Number],
      required: true
    },
    address: String
  },
  contactPhone: {
    type: String,
    required: true
  },
  salary: {
    min: Number,
    max: Number,
    currency: {
      type: String,
      default: 'INR'
    }
  },
  duration: String,
  timing: String,
  requirements: [String],
  benefits: [String],
  createdBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User',
   
  },
  isVerified: {
    type: Boolean,
    default: false
  },
  verificationStatus: {
    type: String,
    enum: ['pending', 'verified', 'rejected'],
    default: 'pending'
  },
  verifiedBy: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'User'
  },
  verifiedAt: Date,
  rating: {
    type: Number,
    default: 0
  },
  reviewCount: {
    type: Number,
    default: 0
  },
  views: {
    type: Number,
    default: 0
  },
  applications: [{
    user: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'User'
    },
    appliedAt: {
      type: Date,
      default: Date.now
    },
    status: {
      type: String,
      enum: ['pending', 'accepted', 'rejected'],
      default: 'pending'
    }
  }],
  images: [String],
  expiresAt: Date,
  isActive: {
    type: Boolean,
    default: true
  },
  createdAt: {
    type: Date,
    default: Date.now
  }
});

// Index for location-based queries and search
listingSchema.index({ location: '2dsphere' });
listingSchema.index({ title: 'text', description: 'text' });
listingSchema.index({ type: 1, category: 1 });
listingSchema.index({ createdAt: -1 });

// Virtual for calculating distance (will be computed in queries)
listingSchema.virtual('distance').get(function() {
  return null; // This will be computed in queries using $geoNear
});

module.exports = mongoose.model('Listing', listingSchema);