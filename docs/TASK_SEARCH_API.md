# Task Search API Documentation

## Endpoint
`POST /api/tasks/search`

## Authentication
Requires authentication via Laravel Sanctum. Include the Bearer token in the Authorization header.

## Search Parameters

All parameters are optional. You can combine multiple parameters to create complex searches.

### Basic Search Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `name` | string | Search for tasks by name (partial match) | `"Bug Fix"` |
| `status` | string | Filter by task status | `"in_progress"`, `"completed"`, `"pending"` |
| `importance` | string | Filter by task importance | `"high"`, `"medium"`, `"low"` |
| `start_date` | date | Filter tasks due on or after this date | `"2025-01-01"` |
| `end_date` | date | Filter tasks due on or before this date | `"2025-12-31"` |
| `assignee_id` | integer | Filter tasks assigned to a specific user | `123` |
| `project_id` | integer | Filter tasks within a specific project | `456` |
| `per_page` | integer | Number of results per page (1-100, default: 15) | `20` |

## Search Logic

- **AND Logic**: When multiple parameters are provided, all conditions must be met
- **Partial Matching**: Name search uses partial matching (e.g., "Bug" will find "Bug Fix" and "Debug Issue")
- **Date Range**: Both start_date and end_date are inclusive
- **Permissions**: Users can only search tasks they have access to:
  - Tasks from projects they authored
  - Tasks from projects where they are contributors
  - Tasks assigned to them

## Example Requests

### Search by Name
```bash
curl -X POST /api/tasks/search \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"name": "Bug"}'
```

### Search by Status and Importance
```bash
curl -X POST /api/tasks/search \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress", "importance": "high"}'
```

### Search by Date Range
```bash
curl -X POST /api/tasks/search \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"start_date": "2025-01-01", "end_date": "2025-12-31"}'
```

### Search by Assignee
```bash
curl -X POST /api/tasks/search \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"assignee_id": 123}'
```

### Complex Search
```bash
curl -X POST /api/tasks/search \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Feature",
    "status": "pending",
    "importance": "medium",
    "start_date": "2025-01-01",
    "per_page": 25
  }'
```

## Response Format

### Success Response (200)
```json
{
  "is_ok": true,
  "message": "Search completed successfully",
  "payload": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "High Priority Bug Fix",
        "status": "in_progress",
        "importance": "high",
        "due_date": "2025-12-31",
        "description": "Fix critical bug in production",
        "author": {
          "id": 1,
          "name": "John Doe"
        },
        "project": {
          "id": 1,
          "name": "Test Project"
        },
        "assignments": [
          {
            "id": 1,
            "user": {
              "id": 2,
              "name": "Jane Smith"
            }
          }
        ]
      }
    ],
    "first_page_url": "...",
    "from": 1,
    "last_page": 1,
    "last_page_url": "...",
    "next_page_url": null,
    "path": "...",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### Error Response (422 - Validation Error)
```json
{
  "message": "The end date must be a date after or equal to start date.",
  "errors": {
    "end_date": ["The end date must be a date after or equal to start date."]
  }
}
```

### Error Response (401 - Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

## Notes

- Date format should be YYYY-MM-DD
- The search is case-insensitive for text fields
- Results are ordered by creation date (newest first)
- Pagination is included by default with 15 items per page
- Maximum items per page is 100
- The API automatically includes related data (author, project, assignments) for better user experience
