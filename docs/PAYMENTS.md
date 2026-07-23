# Payments

Payment processing is not implemented in this revision.

The database currently implements plans, subscriptions, and usage-limit enforcement for
active reminders. It does not create checkouts, payments, invoices, refunds, coupons, or
signed webhooks. No card data is accepted or stored.

`PAYMENT_DRIVER=mock` is reserved in the environment example but is not presented as a
working checkout.
