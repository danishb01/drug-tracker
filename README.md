# Drug Search and Tracker API

A Laravel-based API service for drug information search and user-specific medication tracking. This service integrates with the National Library of Medicine's RxNorm APIs for comprehensive drug data.

## Features

- **User Authentication**: Register, login, and logout with JWT tokens
- **Drug Search**: Public endpoint to search drugs using RxNorm API
- **Medication Tracking**: Users can add/remove medications from their personal list
- **Rate Limiting**: Prevents abuse of the public search endpoint
- **Caching**: Improves performance by caching RxNorm API responses
- **Comprehensive Testing**: 90%+ test coverage

## Technical Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **API Documentation**: Postman Collection included
- **Testing**: PHPUnit with Mockery

## Architecture

This project follows the **Service Repository Pattern** with:
- **Services**: Handle business logic and external API calls
- **Repositories**: Manage data access and database operations
- **Request Classes**: Handle validation
- **Resource Classes**: Format API responses
- **Controllers**: Handle HTTP requests and responses

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd drug-tracker
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=drug_tracker
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abc123..."
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Logout User
```http
POST /api/logout
Authorization: Bearer {token}
```

### Public Drug Search

#### Search Drugs
```http
GET /api/drugs/search?drug_name=aspirin
```

**Response:**
```json
{
    "data": [
        {
            "rxcui": "1191",
            "drug_name": "Aspirin 325 MG Oral Tablet",
            "base_names": ["Aspirin"],
            "dose_form_group_names": ["Oral Tablet"]
        }
    ]
}
```

**Rate Limiting**: 10 requests per minute per IP address

### User Medication Endpoints (Authenticated)

#### Get User Medications
```http
GET /api/medications
Authorization: Bearer {token}
```

#### Add Medication
```http
POST /api/medications
Authorization: Bearer {token}
Content-Type: application/json

{
    "rxcui": "1191"
}
```

#### Remove Medication
```http
DELETE /api/medications
Authorization: Bearer {token}
Content-Type: application/json

{
    "rxcui": "1191"
}
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DrugController.php
│   │   └── UserMedicationController.php
│   ├── Requests/
│   │   ├── SearchDrugRequest.php
│   │   ├── AddDrugRequest.php
│   │   └── DeleteDrugRequest.php
│   └── Resources/
│       ├── DrugSearchResource.php
│       └── UserMedicationResource.php
├── Models/
│   ├── User.php
│   └── UserMedication.php
├── Services/
│   └── RxNormService.php
└── Repositories/
    └── UserMedicationRepository.php
```

## Key Features Implementation

### Service Repository Pattern
- **RxNormService**: Handles all interactions with the National Library of Medicine API
- **UserMedicationRepository**: Manages user medication data operations

### Request Validation
- Separate request classes for each endpoint with custom validation rules
- Comprehensive error messages for better user experience

### Resource Classes
- **DrugSearchResource**: Formats drug search results
- **UserMedicationResource**: Formats user medication data

### Rate Limiting
- Implemented on the public drug search endpoint
- 10 requests per minute per IP address
- Returns 429 status code when limit exceeded

### Caching
- RxNorm API responses cached for 1 hour
- Reduces API calls and improves performance
- Cache keys based on search terms and RxCUI

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `404`: Not Found
- `422`: Validation Error
- `429`: Too Many Requests
- `500`: Server Error

## Security Features

- JWT token authentication using Laravel Sanctum
- Password hashing with bcrypt
- Input validation and sanitization
- Rate limiting to prevent abuse
- CORS protection

## Performance Optimizations

- Database indexing on frequently queried fields
- API response caching
- Efficient database queries with relationships
- Rate limiting to prevent server overload

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is licensed under the MIT License.
