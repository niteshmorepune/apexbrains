Verify all 4 demo users can authenticate and are redirected to the correct portal.

Write a temporary PHP script to test Auth::attempt() for all 4 demo users, run it, then delete it.

Expected results:
- admin@apexbrains.in → super_admin → /admin/dashboard
- kothrud@apexbrains.in → franchise_admin → /franchise/dashboard  
- arjun@student.in → student/internal → /student/home
- external@test.in → student/external → /external/home

All passwords are `password`.
