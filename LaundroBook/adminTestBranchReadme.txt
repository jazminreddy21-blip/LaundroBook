Admin Side - What's Changed Since I Merged the Front End

After merging the new front-end design with the old backend
functionality, the following functionality was added:


DELIVERY & PICKUP:

Nothing was creating delivery records when a customer picked Home
Pickup and Delivery. The option existed on the booking form but did
nothing on the backend. It now creates two records for that booking,
one for the pickup leg and one for the delivery leg, so each can be
tracked and updated separately.

Scheduled times are now based on the booked slot instead of fixed
placeholders. Pickup is about an hour before the wash starts, and
delivery is about two hours after it ends.

Groundworkers are auto-assigned at booking, and an admin can reassign
them from the delivery or pickup management pages.

I also enforced an order of events. A booking must be marked in
progress before pickup can start, pickup must be completed before
delivery can start, and once delivery is done, the booking
automatically becomes completed.

A booking with pickup or delivery also can't be marked completed from
the booking page until both are completed. An error is shown instead.
Walk-in bookings have no pickup or delivery, so they can still be
completed at any time.


CUSTOMER TRACKING:

There is 3 tracking pages. A general order
tracker works for any booking, including walk-ins who previously had
no way to check on their laundry. A delivery tracker and a pickup
tracker each show a progress bar and who is handling the order.


HEAVY WASH BUGS:

Heavy Wash takes two time slots, so it creates two database rows. That
caused several bugs: it appeared as two bookings in the admin table,
it doubled revenue, it inflated the dashboard counts, updating one
row's status left the other unchanged, and completing a delivery only
completed one of the two rows.

All of that is fixed. Each row still has its own booking reference, 
because the reference column must be unique and two rows can't share one. 
Instead, the admin table groups the two rows and shows them as a single booking.

ENQUIRIES:

There's now an enquiry management page where admins can
view messages and mark them as responded to.


MULTIPLE ADMINS:

The system now supports more than one admin. The super admin is
whichever admin was created first. If that account is ever deleted,
the next-oldest admin takes over automatically.

Only the super admin can add or remove admins. Removal has two safety
checks: the last remaining admin can't be deleted, and neither can an
admin tied to real bookings or other data, since there's no way to
reassign that data yet.


EMAIL NOTIFICATIONS:

Customers are now emailed when their order is marked complete, and
when a booking is cancelled.


NOT BUGS:

Every booking, machine, service, and so on is attributed to the same admin no matter
who is logged in. That's intentional, since all admins have the same
access and nothing needs to know which admin did what.
