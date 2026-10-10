<?php

namespace App\Imports;

use RuntimeException;

/**
 * A single import row failed validation. The message is shown to
 * the user prefixed with the spreadsheet row number.
 */
class ImportRowException extends RuntimeException {}
