<?php

namespace Svea;

abstract class SubscriptionType {
    const RECURRING = 'RECURRING';
    const RECURRINGCAPTURE = 'RECURRINGCAPTURE';
    const ONECLICK = 'ONECLICK';
    const ONECLICKCAPTURE = 'ONECLICKCAPTURE';
}
