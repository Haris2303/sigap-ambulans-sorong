<?php

namespace App\Exceptions;

use DomainException;

/** Perpindahan status yang tidak ada di tabel mesin status (RANCANGAN bagian 5). */
class TransisiTidakValidException extends DomainException
{
}
