<?php

namespace Idkwhoami\FluxFiles\Support;

use Illuminate\Support\Facades\Facade;

/**
 * FluxFiles Facade for helper functions
 *
 * @method static string icon($file) Get appropriate icon for file
 * @method static string formatSize($bytes) Format bytes to human readable
 * @method static string formatDate($date) Format date according to locale settings
 * @method static bool validateType($file) Check if file type is allowed
 */
class FluxFiles extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'flux-files';
    }
}
