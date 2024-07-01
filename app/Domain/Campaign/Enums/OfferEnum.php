<?php

namespace App\Domain\Campaign\Enums;

enum OfferEnum
{
    // Status
    const Pending = 'pending';
    const Approved = 'approved';
    const Rejected = 'rejected';

    // Negotiation
    const FreeIgs = 'Free IG story';
    const Discount = 'Potongan harga';
    const FreeIgsDiscount = 'Free Ig Story + Potongan harga';
    const NoNego = 'No nego';

    // Transfer Status
    const Paid = 'paid';
    const Unpaid = 'unpaid';

    const Status = [
        self::Pending,
        self::Approved,
        self::Rejected
    ];

    const TransferStatus = [
        self::Paid,
        self::Unpaid
    ];

    const Negotiation = [
        self::NoNego,
        self::FreeIgs,
        self::Discount,
        self::FreeIgsDiscount,
    ];
}
