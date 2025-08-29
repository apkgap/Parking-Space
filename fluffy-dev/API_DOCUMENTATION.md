# API Documentation - Parking Space Management System

## Overview

This document provides comprehensive documentation for all REST API endpoints and AJAX handlers in the Parking Space Management System. The system includes car rental, driver management, parking booking, and various service management features.

## Base URL
```
/wp-json/
```

## Authentication

### WordPress REST API Authentication
- **Method**: Bearer Token (when logged in)
- **Permissions**: Some endpoints require specific user capabilities
- **AJAX Nonce**: Required for AJAX endpoints

---

## 1. Car Rental Management API

### REST API Endpoints

#### GET `/carrental/v1/rentals`
Get all car rentals with pagination, search and filtering.

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 12)
- `search` (string, optional): Search term
- `status` (string, optional): Filter by rental status
- `date_from` (string, optional): Filter from date (YYYY-MM-DD)
- `date_to` (string, optional): Filter to date (YYYY-MM-DD)
- `date_range` (string, optional): Date range filter

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "title": "Car Rental Booking - John Doe (CR20241201-ABC123)",
      "status": "publish",
      "booking_reference": "CR20241201-ABC123",
      "personal_information": {
        "full_name": "John Doe",
        "email": "john@example.com",
        "phone_number": "+66-123-456789",
        "line_id_whatsapp": "@johndoe"
      },
      "travel_information_outbound_trip": {
        "depart_start_location": "Airport",
        "depart_destination": "Hotel",
        "depart_date": "2024-12-01",
        "depart_time": "09:00"
      }
    }
  ],
  "pagination": {
    "total": 100,
    "total_pages": 10,
    "current_page": 1,
    "per_page": 12
  }
}
```

#### GET `/carrental/v1/rentals/{id}`
Get specific car rental by ID.

**Path Parameters:**
- `id` (integer): Car rental post ID

#### GET `/carrental/v1/rentals/export`
Export car rentals data.

**Query Parameters:**
- `format` (string, optional): Export format (csv, excel, pdf)

#### POST `/carrental/v1/rentals`
Create new car rental booking.

**Permissions Required:** `edit_posts`

**Request Body:**
```json
{
  "personal_information": {
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+66-123-456789",
    "line_id_whatsapp": "@johndoe"
  },
  "travel_information_outbound_trip": {
    "depart_start_location": "Airport",
    "depart_destination": "Hotel",
    "depart_date": "2024-12-01",
    "depart_time": "09:00"
  },
  "booking_details": {
    "car_type": "SUV",
    "adult_count": 2,
    "child_count": 0
  },
  "flight_information": {
    "arrival_flight_no": "TG123",
    "arrival_flight_time": "08:30",
    "departure_flight_no": "TG456",
    "departure_flight_time": "22:00"
  }
}
```

#### PUT `/carrental/v1/rentals/{id}`
Update existing car rental.

**Permissions Required:** `edit_posts`

#### DELETE `/carrental/v1/rentals/{id}`
Delete car rental booking.

**Permissions Required:** `delete_posts`

### AJAX Endpoints

#### `insert_car_rent_acf`
Create car rental booking via AJAX.

**Action:** `wp_ajax_insert_car_rent_acf` / `wp_ajax_nopriv_insert_car_rent_acf`

**Request Method:** POST

**Required Fields:**
```javascript
{
  nonce: 'security_nonce',
  fields: {
    personal_information: {
      full_name: 'John Doe',
      email: 'john@example.com',
      phone_number: '+66-123-456789',
      line_id_whatsapp: '@johndoe'
    },
    travel_information_outbound_trip: {
      depart_start_location: 'Airport',
      depart_destination: 'Hotel',
      depart_date: '2024-12-01',
      depart_time: '09:00'
    },
    booking_details: {
      car_type: 'SUV',
      adult_count: 2,
      child_count: 0
    },
    flight_information: {
      arrival_flight_no: 'TG123',
      arrival_flight_time: '08:30',
      departure_flight_no: 'TG456',
      departure_flight_time: '22:00'
    }
  }
}
```

---

## 2. Taxi Driver Management API

### REST API Endpoints

#### GET `/taxi/v1/drivers`
Get all taxi drivers with pagination, search and filtering.

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 12)
- `search` (string, optional): Search term
- `status` (string, optional): Filter by driver status
- `orderby` (string, optional): Sort field
- `order` (string, optional): Sort order (ASC/DESC)

#### GET `/taxi/v1/drivers/{id}`
Get specific taxi driver by ID.

#### GET `/taxi/v1/drivers/export`
Export taxi drivers data.

#### POST `/taxi/v1/drivers`
Create new taxi driver.

**Permissions Required:** `edit_posts`

#### PUT `/taxi/v1/drivers/{id}`
Update existing taxi driver.

**Permissions Required:** `edit_posts`

#### DELETE `/taxi/v1/drivers/{id}`
Delete taxi driver.

**Permissions Required:** `delete_posts`

---

## 3. Parking Booking Management API

### REST API Endpoints

#### GET `/parking/v1/bookings`
Get all parking bookings with pagination, search and filtering.

**Query Parameters:**
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 100)
- `search` (string, optional): Search term
- `status` (string, optional): Filter by booking status
- `start_date` (string, optional): Start date filter
- `date_from` (string, optional): From date filter
- `date_to` (string, optional): To date filter

#### GET `/parking/v1/bookings/{id}`
Get specific parking booking by ID.

#### GET `/parking/v1/bookings/export`
Export parking bookings data.

#### GET `/parking/v1/today-bookings`
Get today's parking bookings.

#### GET `/parking/v1/monthly-bookings`
Get monthly parking bookings.

#### POST `/parking/v1/bookings`
Create new parking booking.

**Permissions Required:** `edit_posts`

#### PUT `/parking/v1/bookings/{id}`
Update existing parking booking.

**Permissions Required:** `edit_posts`

#### DELETE `/parking/v1/bookings/{id}`
Delete parking booking.

**Permissions Required:** `delete_posts`

---

## 4. Service Management API

### AJAX Endpoints

#### Service Car Rental Management
**Action:** `wp_ajax_service_carrent_management` / `wp_ajax_nopriv_service_carrent_management`

#### Service Customer Management
**Action:** `wp_ajax_service_customer_management` / `wp_ajax_nopriv_service_customer_management`

#### Add Driver Job
**Action:** `wp_ajax_add_driver_job` / `wp_ajax_nopriv_add_driver_job`

---

## 5. Authentication API

### AJAX Endpoints

#### User Registration
**Action:** `wp_ajax_signup_user` / `wp_ajax_nopriv_signup_user`

**Request Method:** POST

**Required Fields:**
```javascript
{
  nonce: 'signup_nonce',
  username: 'johndoe',
  email: 'john@example.com',
  password: 'password123',
  confirm_password: 'password123'
}
```

#### User Login
**Action:** `wp_ajax_signin_user` / `wp_ajax_nopriv_signin_user`

**Request Method:** POST

**Required Fields:**
```javascript
{
  nonce: 'signin_nonce',
  username: 'johndoe',
  password: 'password123',
  remember: true
}
```

#### Password Reset
**Action:** `wp_ajax_forgot_password` / `wp_ajax_nopriv_forgot_password`

**Request Method:** POST

**Required Fields:**
```javascript
{
  nonce: 'forgot_password_nonce',
  user_login: 'john@example.com'
}
```

---

## 6. Utility API

### REST API Endpoints

#### GET `/carrent/car-locations`
Get all car location posts.

**Response:**
```json
[
  {
    "id": 1,
    "title": "Bangkok Airport"
  },
  {
    "id": 2,
    "title": "Pattaya Beach"
  }
]
```

---

## 7. Custom Post Types

### Car Rentals (`car_rentals`)
**ACF Fields:**
- `personal_information` (group)
  - `full_name` (text)
  - `email` (email)
  - `phone_number` (text)
  - `line_id_whatsapp` (text)
- `travel_information_outbound_trip` (group)
  - `depart_start_location` (text)
  - `depart_start_location_note` (textarea)
  - `depart_destination` (text)
  - `depart_destination_note` (textarea)
  - `depart_date` (date_picker)
  - `depart_time` (text)
- `travel_information_return_trip` (group) - conditional
- `booking_details` (group)
  - `car_type` (text)
  - `adult_count` (number)
  - `child_count` (number)
  - `remark_booking` (textarea)
- `flight_information` (group)
  - `arrival_flight_no` (text)
  - `arrival_flight_time` (text)
  - `departure_flight_no` (text)
  - `departure_flight_time` (text)
  - `discount_code` (text)
  - `remark_flight` (textarea)

### Taxi Driver (`taxi-driver`)
**ACF Fields:**
- `driver_code` (text)
- `driver_name` (text)
- `driver_phone` (text)
- `driver_email` (email)
- `license_number` (text)
- `vehicle_type` (select)
- `status` (select)

### Parking Booking (`parking_booking`)
**ACF Fields:**
- `customer_name` (text)
- `customer_email` (email)
- `customer_phone` (text)
- `booking_date` (date_picker)
- `booking_time` (text)
- `duration_hours` (number)
- `parking_location` (text)
- `vehicle_type` (select)
- `license_plate` (text)
- `booking_status` (select)

---

## 8. Error Responses

### Common Error Codes
```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": {
    "status": 403
  }
}
```

```json
{
  "code": "rest_invalid_param",
  "message": "Invalid parameter(s): search",
  "data": {
    "status": 400,
    "params": {
      "search": "Invalid search parameter"
    }
  }
}
```

### AJAX Error Response
```json
{
  "success": false,
  "message": "Error message here"
}
```

---

## 9. Security Features

### Nonce Protection
All AJAX endpoints require WordPress nonces for security:
```javascript
wp_localize_script('script-handle', 'ajax_object', {
  'ajax_url': admin_url('admin-ajax.php'),
  'nonce': wp_create_nonce('action_name')
});
```

### Permission Checks
- REST API endpoints check user capabilities
- Admin-only operations require `edit_posts` or `delete_posts` capabilities
- Public endpoints use `__return_true` for open access

---

## 10. Data Export Features

### Supported Formats
- CSV (default)
- Excel
- PDF

### Export Endpoints
- `/carrental/v1/rentals/export`
- `/taxi/v1/drivers/export`
- `/parking/v1/bookings/export`

**Query Parameters:**
- `format` (string, optional): Export format (csv/excel/pdf)
- `date_from` (string, optional): Date range start
- `date_to` (string, optional): Date range end

---

## 11. Email Notifications

### Car Rental Booking Confirmation
- **Trigger:** After successful booking creation
- **Recipients:** Customer and admin
- **Template:** HTML email with booking details

### Email Template Features
- Customer booking summary
- Travel information (outbound/return)
- Flight details
- Contact information
- Company branding

---

## 12. File Structure

```
fluffy-dev/
├── customize/
│   ├── control/
│   │   ├── carrent.php              # Car rental AJAX handlers
│   │   ├── signup.php               # User registration
│   │   ├── signin.php               # User login
│   │   └── service-management.php   # Service management
│   └── js/
│       ├── carrent.js               # Car rental frontend
│       └── ajax-carrent.js          # AJAX utilities
├── dashboard/
│   ├── carrental-management/
│   │   ├── carrental.php            # Car rental REST API
│   │   └── js/carrental.js          # Management interface
│   ├── driver-management/
│   │   ├── driver.php               # Driver REST API
│   │   └── js/driver.js             # Management interface
│   └── parking-management/
│       ├── parking.php              # Parking REST API
│       └── js/parking.js            # Management interface
└── API_DOCUMENTATION.md             # This file
```

---

## 13. Development Notes

### WordPress Dependencies
- WordPress 5.0+ (REST API support)
- Advanced Custom Fields (ACF) Pro
- jQuery (for AJAX operations)

### Custom Post Types
- `car_rentals`
- `taxi-driver`
- `parking_booking`
- `car_location`

### User Roles
- `service-customer` (custom role)
- Standard WordPress roles (administrator, editor, etc.)

---

## 14. Testing Guidelines

### REST API Testing
```bash
# Get all car rentals
curl -X GET "https://example.com/wp-json/carrental/v1/rentals" \
  -H "Content-Type: application/json"

# Create new booking (authenticated)
curl -X POST "https://example.com/wp-json/carrental/v1/rentals" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d @booking-data.json
```

### AJAX Testing
```javascript
// Example AJAX call
$.ajax({
  url: ajax_object.ajax_url,
  type: 'POST',
  data: {
    action: 'insert_car_rent_acf',
    nonce: ajax_object.nonce,
    fields: bookingData
  },
  success: function(response) {
    if (response.success) {
      console.log('Booking created:', response.data);
    }
  }
});
```

---

## 15. Maintenance & Support

### Regular Tasks
- Monitor error logs for API failures
- Update nonces regularly for security
- Backup database before major changes
- Test API endpoints after WordPress updates

### Support Contacts
- Technical issues: Check WordPress error logs
- API documentation updates: Update this file
- Security concerns: Review permission callbacks

---

*Last Updated: December 2024*
*Version: 1.0.0*
