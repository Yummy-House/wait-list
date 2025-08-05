# YummyHouse Waitlist API

A PHP-based waitlist and survey collection system for YummyHouse food delivery app.

## Features

- Email collection for waitlist
- Survey data collection with 4 predefined questions
- Admin dashboard for viewing statistics
- CSV export functionality
- MySQL database storage
- RESTful API endpoints

## Setup

1. Make sure you have XAMPP running with Apache and MySQL
2. Update database configuration in `includes/loader.php`
3. The database table will be created automatically on first use

## API Endpoints

### POST /api/waitlist.php
Add a new email to the waitlist with survey responses.

**Request Body:**
```json
{
  "email": "user@example.com",
  "survey": {
    "1": "Social Media",
    "2": "Food Lover",
    "3": ["Online Ordering", "Delivery Tracking"],
    "4": "Weekly",
    "other_feedback": "Additional comments"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Thank you for joining our waitlist! We'll notify you when we launch.",
  "data": {
    "id": 123
  }
}
```

### GET /api/admin.php?action=stats
Get waitlist statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 150,
    "sources": [
      {"how_heard": "Social Media", "count": "45"},
      {"how_heard": "Friend", "count": "30"}
    ],
    "user_types": [
      {"user_type": "Food Lover", "count": "120"},
      {"user_type": "Restaurant Owner", "count": "30"}
    ],
    "ordering_frequency": [
      {"ordering_frequency": "Weekly", "count": "60"},
      {"ordering_frequency": "Monthly", "count": "40"}
    ]
  }
}
```

### GET /api/admin.php?action=entries
Get all waitlist entries with pagination.

**Parameters:**
- `limit` (optional): Number of entries to return (default: 100)
- `offset` (optional): Number of entries to skip (default: 0)

### GET /api/admin.php?action=export
Export all waitlist data as CSV file.

## Survey Questions

The system supports these 4 predefined survey questions:

1. **How did you hear about us?**
   - Options: Social Media, Friend, Advertisement, Other

2. **Are you a restaurant owner or a food lover?**
   - Options: Restaurant Owner, Food Lover

3. **What features would you like to see in our app?**
   - Options: Online Ordering, Delivery Tracking, Restaurant Reviews, Personalized Recommendations, Other
   - Multiple selection allowed

4. **How often do you order food online?**
   - Options: Daily, Weekly, Monthly, Rarely

## Database Schema

The `waitlist` table structure:

```sql
CREATE TABLE waitlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    how_heard VARCHAR(100) DEFAULT NULL,
    user_type VARCHAR(50) DEFAULT NULL,
    desired_features JSON DEFAULT NULL,
    ordering_frequency VARCHAR(50) DEFAULT NULL,
    other_feedback TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);
```

## Files Structure

```
├── api/
│   ├── waitlist.php       # Main API endpoint for collecting emails/surveys
│   └── admin.php          # Admin API for stats and export
├── src/
│   ├── Database.php       # Database connection class
│   └── Waitlist.php       # Waitlist business logic
├── includes/
│   └── loader.php         # Bootstrap file
├── index.html             # Frontend form for testing
├── admin.html             # Admin dashboard
└── README.md              # This file
```

## Usage Examples

### JavaScript Frontend Integration

```javascript
const surveyData = {
  email: "user@example.com",
  survey: {
    "1": "Social Media",           // How heard about us
    "2": "Food Lover",             // User type
    "3": ["Online Ordering"],      // Desired features (array)
    "4": "Weekly"                  // Ordering frequency
  }
};

fetch('./api/waitlist.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify(surveyData)
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Successfully added to waitlist');
  } else {
    console.error('Error:', data.message);
  }
});
```

### cURL Example

```bash
curl -X POST http://localhost/yummyhouse-waitlist/api/waitlist.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "survey": {
      "1": "Friend",
      "2": "Food Lover",
      "3": ["Online Ordering", "Delivery Tracking"],
      "4": "Daily"
    }
  }'
```

## Security Considerations

- Email validation is performed server-side
- SQL injection protection through prepared statements
- CORS headers included for cross-origin requests
- Input sanitization for all user data

## Customization

To modify the survey questions, update both:
1. The frontend HTML/JavaScript in `index.html`
2. The mapping logic in `api/waitlist.php`
3. The database schema if needed

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `201`: Created (new waitlist entry)
- `400`: Bad Request (validation errors)
- `405`: Method Not Allowed
- `500`: Internal Server Error
