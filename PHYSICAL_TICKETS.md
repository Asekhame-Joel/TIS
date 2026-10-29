# Physical ticket operations

The physical-ticket module is separate from Paystack and the existing online ticket system.

## One-time deployment

1. Deploy the application files.
2. In cPanel phpMyAdmin, select the TIS database and run `database/migrations/2026-10-21-physical-tickets.sql` from the Git repository.
   If physical tickets are already installed, also run `database/migrations/2026-10-23-manual-payment-tickets.sql` once. New installations should run both files in date order.
3. In cPanel Terminal, create the first administrator from the deployed application:

   ```bash
   cd /home/theipqie/public_html
   php scripts/create-staff-user.php admin "Administrator Name" admin
   ```

   Enter a password of at least 12 characters when prompted. The password is stored only as a secure hash.

4. Create each entrance staff member with the `gate` role:

   ```bash
   php scripts/create-staff-user.php entrance1 "Entrance Staff Name" gate
   ```

5. Sign in at `/staff-login`.

## Administrator workflow

- Open `/staff-tickets`.
- Enter a quantity from 1 to 1,000 and choose **Generate codes**.
- Use **Printable list** for a print-friendly list or **Export CSV** to download all physical-ticket records.
- Select tickets, enter a vendor name, and choose **Assign selected**.
- Search by code or vendor and select one or more tickets.
- Choose **Mark sold** to confirm selected Unsold tickets as sold.
- Choose **Mark unsold** to reverse selected Sold tickets that were marked sold by mistake.
- Choose **Check in** to admit selected Sold tickets.
- Choose **Undo check-in** to return selected Checked-in tickets to Sold so they can be admitted again.

### Manual payment workflow

- Confirm the attendee's payment outside the website first.
- In **Manual payment → Assign a named ticket**, enter their name, email, phone, category, amount received, payment method and optional payment reference.
- The amount must match the configured face value for the selected category.
- Choose **Assign ticket and create PDF**. The server locks and selects one random Unsold code, marks it Sold, and creates the normal branded ticket in one database transaction.
- Use **View ticket** or **Download PDF** from the success panel. Ticket email delivery is queued through the existing ticket job processor.
- The attendee's name and email appear in the inventory and CSV export. The assigned `TIS-########` code works in the existing entrance verifier.
- If no Unsold codes remain, no order is created; generate more physical codes and try again.

Reversing a manually assigned ticket with **Mark unsold** invalidates its private ticket link and releases the physical code for a future assignment.

Each action only changes tickets in the correct starting state. Unsold tickets cannot be checked in, and Checked-in tickets must be explicitly unchecked before they can be marked Unsold.

## Entrance workflow

- Open `/staff-verify` on a phone and sign in with an admin or gate-staff account.
- Enter a physical code. Spaces and letter case are ignored.
- Looking up a code never checks it in.
- A Sold ticket displays a **Check In** button.
- Administrators also see **Undo Check-in** on an already used ticket; gate staff cannot reverse admissions.
- The database update changes only a Sold row, atomically, so simultaneous devices cannot both check in the same ticket.
- If the database connection fails, the page explicitly says verification is unavailable and staff must not admit the ticket.

## Status meanings

- `unsold`: generated, but sale not yet confirmed.
- `sold`: sale confirmed and awaiting entry.
- `checked_in`: already admitted; the time and staff member are retained.
