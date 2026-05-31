# ltw03g06

## First Delivery
- Signup page: Accessed by clicking the "JOIN US" button on the main page.
- Profile page: Accessed by clicking the profile icon in the top right corner.
- Classes page.
Run using php -S localhost:9000

## Mockup
(https://www.figma.com/design/hI5sROxEz4giQGAlM8Ryiz/GYM-MOCKUP?node-id=0-1&t=E1JRo00MHPRfN3h8-1)

## UML Domain Model
<img src="UML.png" alt="UML Domain Model" width="200" align="center">

## Features

**All users:**
- [X] Register a new account.
- [X] Log in and out.
- [X] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

The class schedule page now uses AJAX live search so filters update the session list without a full page reload.

**Trainers:**
- [X] Manage their public profile, including bio, specializations, and certifications.
- [X] View the roster of members enrolled in their classes.
- [X] Track and manage their assigned class schedule.

**Admins:**
- [X] Manage members and trainers (create, update, and deactivate accounts).
- [X] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [X] Manage equipment in the main training area (add, update availability status, and remove items).
- [X] Elevate a user to admin status.
- [X] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [x] Trainer analytics dashboard for attendance, ratings, and class engagement.
- [x] Admin analytics dashboard for popular classes, equipment usage, and member retention.

## Running

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

## Credentials

Template profiles (use these as examples when creating test accounts):

- **Admin template:** admin@example.com / p4s5w0rd
- **Member template:** member@example.com / 1234
- **Trainer template:** trainer@example.com / 1234

Seeded trainer accounts

```
reed@ltwgym.com:1234
elena@ltwgym.com:1234
cannons@ltwgym.com:1234
nora@ltwgym.com:1234
leo@ltwgym.com:1234
mila@ltwgym.com:1234
```

Seeded member accounts

```
alex@ltwgym.com:1234
sophia@ltwgym.com:1234
daniel@ltwgym.com:1234
maria@ltwgym.com:1234
noah@ltwgym.com:1234
ava@ltwgym.com:1234
luca@ltwgym.com:1234
zoe@ltwgym.com:1234
max@ltwgym.com:1234
iris@ltwgym.com:1234
henry@ltwgym.com:1234
ella@ltwgym.com:1234
olivia@ltwgym.com:1234
ethan@ltwgym.com:1234
grace@ltwgym.com:1234
felix@ltwgym.com:1234
maya@ltwgym.com:1234
julian@ltwgym.com:1234
ruby@ltwgym.com:1234
adam@ltwgym.com:1234
chloe@ltwgym.com:1234
bruno@ltwgym.com:1234
ines@ltwgym.com:1234
tom@ltwgym.com:1234
sara@ltwgym.com:1234
dylan@ltwgym.com:1234
marta@ltwgym.com:1234
ricardo@ltwgym.com:1234
julia@ltwgym.com:1234
paulo@ltwgym.com:1234
nina@ltwgym.com:1234
vasco@ltwgym.com:1234
lara@ltwgym.com:1234
tiago@ltwgym.com:1234
beatriz@ltwgym.com:1234
ruben@ltwgym.com:1234
mafalda@ltwgym.com:1234
rodrigo@ltwgym.com:1234
joana@ltwgym.com:1234
pedro@ltwgym.com:1234
ana@ltwgym.com:1234
rita@ltwgym.com:1234
```
