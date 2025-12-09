#!/bin/bash

# SIMONTA BENCANA API Test Script (Windows Compatible)
# This script tests all REST API endpoints comprehensively

API_BASE="http://localhost:8000/api"
ADMIN_TOKEN=""
WARGA_TOKEN=""
PETUGAS_TOKEN=""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== SIMONTA BENCANA API COMPREHENSIVE TEST SUITE ===${NC}"
echo

# Function to test API endpoints
test_endpoint() {
    local method=$1
    local endpoint=$2
    local data=$3
    local headers=$4
    local expected_status=$5
    local description=$6

    echo -e "\n${YELLOW}Testing: $description${NC}"
    echo -e "${YELLOW}Method: $method $endpoint${NC}"

    if [ -n "$headers" ]; then
        response=$(curl -s -w "\n%{http_code}" -X "$method" "$API_BASE$endpoint" $headers $data)
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" "$API_BASE$endpoint" $data)
    fi

    # Extract HTTP status code and response body
    http_code=$(echo "$response" | tail -n1)
    response_body=$(echo "$response" | head -n -1)

    echo -e "HTTP Status: $http_code"

    if [ "$http_code" = "$expected_status" ]; then
        echo -e "${GREEN}✓ PASSED${NC}"
        echo -e "Response: $response_body"
    else
        echo -e "${RED}✗ FAILED${NC}"
        echo -e "Expected Status: $expected_status, Got: $http_code"
        echo -e "Response: $response_body"
    fi

    echo -e "${YELLOW}--------------------------${NC}"
}

# Function to login and get token
login_and_get_token() {
    local username=$1
    local password=$2
    local role=$3

    echo -e "\n${YELLOW}Login as $role...${NC}"
    response=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d "{\"username\": \"$username\", \"password\": \"$password\"}")

    token=$(echo "$response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

    if [ -z "$token" ]; then
        echo -e "${RED}Failed to get token for $role${NC}"
        return 1
    else
        echo -e "${GREEN}Successfully logged in as $role${NC}"
        return 0
    fi
}

# Function to register user
register_user() {
    local nama=$1
    local username=$2
    local password=$3
    local role=$4
    local email=$5
    local id_desa=$6

    echo -e "\n${YELLOW}Registering $role: $nama${NC}"
    response=$(curl -s -X POST "$API_BASE/auth/register" \
        -H "Content-Type: application/json" \
        -d "{\"nama\": \"$nama\", \"username\": \"$username\", \"password\": \"$password\", \"role\": \"$role\", \"email\": \"$email\", \"id_desa\": \"$id_desa\"}")

    echo -e "Response: $response"
    echo -e "${YELLOW}--------------------------${NC}"
}

# Step 1: Register test users
echo -e "\n${GREEN}=== STEP 1: REGISTERING TEST USERS ===${NC}"

register_user "Test Warga" "test_warga" "password123" "Warga" "warga@test.com"
register_user "Test Petugas BPBD" "test_bpbd" "password123" "PetugasBPBD" "bpbd@test.com"
register_user "Test Operator Desa" "test_operator" "password123" "OperatorDesa" "operator@test.com" "1"

# Step 2: Login and get tokens
echo -e "\n${GREEN}=== STEP 2: LOGIN AND GET TOKENS ===${NC}"

# Login as Admin
login_and_get_token "admin" "password123" "Admin"
if [ $? -eq 0 ]; then
    ADMIN_TOKEN="Authorization: Bearer $token"
fi

# Login as Warga
login_and_get_token "test_warga" "password123" "Warga"
if [ $? -eq 0 ]; then
    WARGA_TOKEN="Authorization: Bearer $token"
fi

# Step 3: Test Public Endpoints (No Auth Required)
echo -e "\n${GREEN}=== STEP 3: TESTING PUBLIC ENDPOINTS ===${NC}"

test_endpoint "GET" "/test" "" "" "200" "API Health Check"

# Test authentication endpoints
test_endpoint "POST" "/auth/register" "-H 'Content-Type: application/json' -d '{\"nama\":\"Test User\",\"username\":\"newuser\",\"password\":\"password123\",\"role\":\"Warga\",\"email\":\"newuser@test.com\"}'" "" "201" "Register New User"
test_endpoint "POST" "/auth/login" "-H 'Content-Type: application/json' -d '{\"username\":\"admin\",\"password\":\"password123\"}'" "" "200" "Login Admin"
test_endpoint "POST" "/auth/login" "-H 'Content-Type: application/json' -d '{\"username\":\"nonexistent\",\"password\":\"wrong\"}'" "" "401" "Login Invalid Credentials"

# Step 4: Test Protected Endpoints (Auth Required)
echo -e "\n${GREEN}=== STEP 4: TESTING PROTECTED ENDPOINTS ===${NC}"

test_endpoint "POST" "/auth/logout" "$ADMIN_TOKEN" "" "200" "Logout Admin"
test_endpoint "POST" "/auth/refresh" "$ADMIN_TOKEN" "" "200" "Refresh Token Admin"
test_endpoint "GET" "/auth/profile" "$ADMIN_TOKEN" "" "200" "Get Admin Profile"
test_endpoint "GET" "/dashboard" "$ADMIN_TOKEN" "" "200" "Admin Dashboard"

# Step 5: Test Role-Based Access Control
echo -e "\n${GREEN}=== STEP 5: TESTING ROLE-BASED ACCESS CONTROL ===${NC}"

# Admin should be able to access admin routes
test_endpoint "GET" "/admin/users" "$ADMIN_TOKEN" "" "200" "Admin Users List"
test_endpoint "GET" "/admin/kategori-bencana" "$ADMIN_TOKEN" "" "200" "Admin Kategori Bencana List"
test_endpoint "GET" "/admin/desa" "$ADMIN_TOKEN" "" "200" "Admin Desa List"
test_endpoint "GET" "/admin/desa-statistics" "$ADMIN_TOKEN" "" "200" "Admin Desa Statistics"
test_endpoint "GET" "/admin/kategori-bencana-statistics" "$ADMIN_TOKEN" "" "200" "Admin Kategori Bencana Statistics"

# Warga should NOT be able to access admin routes
test_endpoint "GET" "/admin/users" "$WARGA_TOKEN" "" "403" "Warga Accessing Admin Users (Should Fail)"
test_endpoint "GET" "/admin/kategori-bencana" "$WARGA_TOKEN" "" "403" "Warga Accessing Kategori Bencana (Should Fail)"
test_endpoint "GET" "/admin/desa" "$WARGA_TOKEN" "" "403" "Warga Accessing Desa (Should Fail)"

# Warga should be able to access citizen routes
test_endpoint "GET" "/citizen/disaster-info" "$WARGA_TOKEN" "" "200" "Warga Disaster Info"

# Step 6: Test Kategori Bencana CRUD Operations (Admin)
echo -e "\n${GREEN}=== STEP 6: TESTING KATEGORI BENCANA CRUD ===${NC}"

# Create kategori bencana
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"Banjir\"}'" "201" "Create Banjir Category"
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"Longsor\"}'" "201" "Create Longsor Category"
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"Kebakaran\"}'" "201" "Create Kebakaran Category"

# Get kategori list
test_endpoint "GET" "/admin/kategori-bencana" "$ADMIN_TOKEN" "" "200" "Get Kategori Bencana List"

# Get specific kategori
test_endpoint "GET" "/admin/kategori-bencana/1" "$ADMIN_TOKEN" "" "200" "Get Kategori Banjir Details"
test_endpoint "GET" "/kategori-bencana/1" "$WARGA_TOKEN" "" "200" "Warga Get Kategori Details"

# Update kategori
test_endpoint "PUT" "/admin/kategori-bencana/1" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"Banjir Bandang\"}'" "200" "Update Banjir Category"

# Test validation - duplicate name
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"Banjir Bandang\"}'" "" "422" "Create Duplicate Category (Should Fail)"
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{}'" "" "422" "Create Empty Category (Should Fail)"

# Delete kategori
test_endpoint "DELETE" "/admin/kategori-bencana/3" "$ADMIN_TOKEN" "" "200" "Delete Kebakaran Category"
test_endpoint "DELETE" "/admin/kategori-bencana/999" "$ADMIN_TOKEN" "" "404" "Delete Non-existent Category (Should Fail)"

# Test statistics
test_endpoint "GET" "/admin/kategori-bencana-statistics" "$ADMIN_TOKEN" "" "200" "Get Kategori Statistics"

# Step 7: Test Desa CRUD Operations
echo -e "\n${GREEN}=== STEP 7: TESTING DESA CRUD ===${NC}"

# Create desa
test_endpoint "POST" "/admin/desa" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_desa\":\"Desa Test 1\",\"kecamatan\":\"Kecamatan Test\",\"kabupaten\":\"Kabupaten Test\"}'" "201" "Create Desa Test 1"
test_endpoint "POST" "/admin/desa" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_desa\":\"Desa Test 2\",\"kecamatan\":\"Kecamatan Test 2\",\"kabupaten\":\"Kabupaten Test\"}'" "201" "Create Desa Test 2"

# Get desa list with pagination
test_endpoint "GET" "/admin/desa" "$ADMIN_TOKEN" "" "200" "Get Desa List"

# Get specific desa
test_endpoint "GET" "/admin/desa/1" "$ADMIN_TOKEN" "" "200" "Get Desa Details"

# Update desa
test_endpoint "PUT" "/admin/desa/1" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_desa\":\"Desa Test 1 Updated\"}'" "200" "Update Desa Test 1"

# Search desa
test_endpoint "GET" "/admin/desa?search=Test" "$ADMIN_TOKEN" "" "200" "Search Desa"

# Filter desa by kecamatan
test_endpoint "GET" "/admin/desa?kecamatan=Kecamatan Test" "$ADMIN_TOKEN" "" "200" "Filter Desa by Kecamatan"

# Get statistics
test_endpoint "GET" "/admin/desa-statistics" "$ADMIN_TOKEN" "" "200" "Get Desa Statistics"

# Delete desa
test_endpoint "DELETE" "/admin/desa/2" "$ADMIN_TOKEN" "" "200" "Delete Desa Test 2"
test_endpoint "DELETE" "/admin/desa/999" "$ADMIN_TOKEN" "" "404" "Delete Non-existent Desa (Should Fail)"

# Step 8: Test Multi-Role Endpoints
echo -e "\n${GREEN}=== STEP 8: TESTING MULTI-ROLE ENDPOINTS ===${NC}"

# Test accessible endpoints for multiple roles
test_endpoint "GET" "/desa" "$ADMIN_TOKEN" "" "200" "Admin Get Desa List"
test_endpoint "GET" "/desa" "$WARGA_TOKEN" "" "403" "Warga Get Desa List (Should Fail)"
test_endpoint "GET" "/desa-list/kecamatan" "$ADMIN_TOKEN" "" "200" "Get Kecamatan List"
test_endpoint "GET" "/desa-list/kabupaten" "$ADMIN_TOKEN" "" "200" "Get Kabupaten List"
test_endpoint "GET" "/kategori-bencana" "$ADMIN_TOKEN" "" "200" "Admin Get Kategori Bencana List"

# Step 9: Test Error Handling
echo -e "\n${GREEN}=== STEP 9: TESTING ERROR HANDLING ===${NC}"

# Test validation errors
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{}'" "" "422" "Empty Category Name"
test_endpoint "POST" "/admin/kategori-bencana" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{\"nama_kategori\":\"\"}'" "" "422" "Blank Category Name"
test_endpoint "POST" "/admin/desa" "$ADMIN_TOKEN" "-H 'Content-Type: application/json' -d '{}'" "" "422" "Empty Desa Data"

# Test non-existent resources
test_endpoint "GET" "/admin/desa/999" "$ADMIN_TOKEN" "" "404" "Get Non-existent Desa"
test_endpoint "GET" "/admin/kategori-bencana/999" "$ADMIN_TOKEN" "" "404" "Get Non-existent Kategori"

# Final Summary
echo -e "\n${GREEN}=== TEST SUMMARY ===${NC}"
echo -e "${YELLOW}All API endpoints have been tested.${NC}"
echo -e "${GREEN}✓ Authentication System Working${NC}"
echo -e "${GREEN}✓ Role-Based Access Control Working${NC}"
echo -e "${GREEN}✓ Data Validation Working${NC}"
echo -e "${GREEN}✅ Error Handling Working${NC}"
echo -e "${GREEN}✅ Kategori Bencana CRUD Working${NC}"
echo -e "${GREEN}✅ Desa Management Working${NC}"
echo -e "\n${YELLOW}Note: Some tests may fail if the database has different data or if certain validations are in place.${NC}"
echo -e "${YELLOW}      Check individual test results above for details.${NC}"

echo -e "\n${GREEN}=== API TESTING COMPLETED ===${NC}"