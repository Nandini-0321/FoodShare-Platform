# FoodShare Test Cases

These test cases are designed to verify the application's functionality after a complete database reset.

## Prerequisites
- Database has been reset using `reset_db.php`.
- Application is running.

## Test Case 1: Donor Registration
**Goal**: Verify a new donor can register successfully.
1.  Navigate to the Registration Page.
2.  Select "Donor" as the user type.
3.  Fill in the following details:
    -   **Name**: Test Donor
    -   **Email**: donor@test.com
    -   **Password**: password123
    -   **Phone**: 9876543210
    -   **Address**: 123 Test St
    -   **City**: Test City
4.  Submit the form.
5.  **Expected Result**: You should be redirected to the Login page or Dashboard with a success message. Check the database `users` table to confirm the new entry.

## Test Case 2: NGO Registration
**Goal**: Verify a new NGO can register successfully.
1.  Navigate to the Registration Page.
2.  Select "NGO" as the user type.
3.  Fill in the following details:
    -   **Name**: Test NGO
    -   **Email**: ngo@test.com
    -   **Password**: password123
    -   **Phone**: 9876543211
    -   **Address**: 456 NGO Rd
    -   **City**: Test City
    -   **NGO Type**: Orphanage
4.  Submit the form.
5.  **Expected Result**: Registration successful. Note: NGO might require admin verification depending on configuration.

## Test Case 3: Volunteer Registration
**Goal**: Verify a new Volunteer can register successfully.
1.  Navigate to the Registration Page.
2.  Select "Volunteer" as the user type.
3.  Fill in the following details:
    -   **Name**: Test Volunteer
    -   **Email**: volunteer@test.com
    -   **Password**: password123
    -   **Phone**: 9876543212
    -   **Address**: 789 Vol St
    -   **City**: Test City
4.  Submit the form.
5.  **Expected Result**: Registration successful.

## Test Case 4: Donor Login & Add Donation
**Goal**: Verify a donor can log in and add a donation.
1.  Navigate to the Login Page.
2.  Log in with `donor@test.com` / `password123`.
3.  Navigate to "Donate Food" / "New Donation".
4.  Fill in donation details:
    -   **Food Type**: Cooked
    -   **Food Name**: Rice and Curry
    -   **Quantity**: 10
    -   **Unit**: Plates
    -   **Expiry**: Set a future time
    -   **Address**: (Should auto-fill or allow entry)
5.  Submit.
6.  **Expected Result**: Donation added successfully and visible in the "My Donations" list.

## Test Case 5: NGO Login & Request Donation
**Goal**: Verify an NGO can log in and request the donation.
1.  Log out (if logged in).
2.  Log in with `ngo@test.com` / `password123`.
3.  Navigate to "Available Food" / "Browse Donations".
4.  You should see the "Rice and Curry" donation.
5.  Click "Request".
6.  **Expected Result**: Request sent successfully. Status of donation might change to "Requested".

## Test Case 6: Volunteer Login & View Tasks
**Goal**: Verify a volunteer can log in.
1.  Log out.
2.  Log in with `volunteer@test.com` / `password123`.
3.  Navigate to Dashboard.
4.  **Expected Result**: Dashboard loads successfully. (Tasks might not be assigned yet depending on the flow).

## Test Case 7: Admin Login (If applicable)
**Goal**: Verify admin access (if you have a default admin or need to create one manually).
1.  *Note*: Since the database was wiped, you might need to manually insert an admin user or register one if the system allows.
2.  **Action**: Check if an admin account is needed for NGO verification.

## Cleanup (Optional)
- Run `reset_db.php` again if you want to clear these test entries.
