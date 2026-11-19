<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A ride could not be done as asked, for a reason the person on the other end
 * should be told about: no money in the wallet, a trip somebody else already
 * took, a trip that has moved on since the screen last looked.
 *
 * These are expected outcomes rather than faults, so the message is written to
 * be shown as it is and nothing here is logged as an error.
 */
class RideException extends RuntimeException
{
}
