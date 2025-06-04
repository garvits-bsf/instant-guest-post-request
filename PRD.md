# Product Requirements Document: Instant Guest Post Request

## Overview
Instant Guest Post Request is a WordPress plugin that streamlines the guest post submission process by providing a front-end form for visitors to submit their content pitches. The plugin automatically saves submissions as draft posts and notifies administrators for review.

## Problem Statement
Blog owners and content managers often receive numerous guest post requests through various channels (email, contact forms, etc.), making it difficult to manage and track submissions effectively. This plugin aims to centralize and automate this process.

## Target Users
- Bloggers and editors running high-traffic content sites
- Community-driven WordPress sites accepting external submissions
- Marketing teams handling influencer outreach and content collaborations

## Core Features

### 1. Frontend Guest Post Submission Form
#### Form Fields
- Post Title (text input)
- Post Content (rich text editor)
- Author Name (text input)
- Author Email (email input)
- Author Bio (textarea)
- Featured Image Upload (image upload)

#### Technical Requirements
- No login required for submission
- Form validation and error handling
- Responsive design using Tailwind CSS
- ForceUI components for consistent UI
- ReactJS implementation where applicable

### 2. Post Management
#### Auto-save Functionality
- Creates draft posts with "pending" status
- Saves author information as post meta
- Attaches uploaded images as featured images
- Assigns default category (configurable)

### 3. Admin Notifications
#### Email Template
- Subject: "New Guest Post Submission: [Post Title]"
- Content:
  - Post title
  - Author information
  - Preview link
  - Approve/Reject action links
  - Admin dashboard link

### 4. Admin Dashboard Interface
#### Settings Page
Location: Settings > Guest Post Plugin

##### Tabs
1. General Settings
   - Default post category
   - Submission limits per IP
   - Moderation toggle
   - reCAPTCHA/hCaptcha integration

2. Notification Settings
   - Email template customization
   - Admin email configuration
   - Auto-reply settings

3. Form Style
   - Theme selection (light/dark)
   - Custom CSS options

### 5. Shortcode Implementation
- `[guest_post_form]` for form placement
- Customizable attributes for form styling

## Technical Specifications

### Frontend Requirements
- Responsive design across all devices
- WCAG 2.1 accessibility compliance
- Tailwind CSS for styling
- ForceUI component library
- ReactJS for dynamic components

### Backend Requirements
- WordPress post type integration
- Custom meta fields for author information
- Email notification system
- Image upload handling
- Security measures (CSRF protection, input sanitization)

### Optional Features
1. Spam Protection
   - reCAPTCHA/hCaptcha integration
   - Honeypot fields
   - Domain/keyword blocklist

2. Analytics & Monitoring
   - Dashboard widget for recent submissions
   - Submission statistics

3. Integration Options
   - Mailchimp/ConvertKit newsletter integration
   - OpenAI Moderation API for content filtering

## Testing Requirements

### Unit Tests
- Form validation
- Post creation
- Email notification
- Image upload
- Settings management

### E2E Tests
- Complete submission workflow
- Admin approval/rejection process
- Email notification delivery
- Shortcode functionality

## Acceptance Criteria

### Functional Requirements
1. Plugin Installation
   - Successful installation and activation
   - No PHP errors or warnings
   - Proper database table creation

2. Form Functionality
   - Working shortcode implementation
   - Successful post creation
   - Proper image upload handling
   - Form validation

3. Admin Features
   - Working settings page
   - Email notification system
   - Post management capabilities

### Non-functional Requirements
1. Performance
   - Page load time < 2 seconds
   - Efficient database queries
   - Optimized image handling

2. Security
   - Input sanitization
   - CSRF protection
   - Secure file uploads

3. Compatibility
   - WordPress 5.0+
   - PHP 7.4+
   - Major browser support

## Development Guidelines

### Code Structure
- Follow WordPress coding standards
- Implement proper namespacing
- Use OOP principles
- Document code with PHPDoc

### AI Integration
- Use Amazon Q for:
  - Code suggestions
  - Terminal commands
  - Development queries

- Use ChatGPT Codex for:
  - Function implementation
  - Test case generation

### Documentation
Required files:
- README.md
- architecture.md
- PRD.md (this document)

## Future Enhancements
1. Advanced Moderation
   - AI-powered content filtering
   - Automated spam detection
   - Content quality scoring

2. Analytics Dashboard
   - Submission trends
   - Author statistics
   - Content performance metrics

3. Integration Options
   - Social media sharing
   - Content calendar integration
   - SEO tools integration
