<?php

if (! function_exists('studly_case')) {
    function studly_case($string)
    {
        return str($string)->studly();
    }
}

if (! function_exists('previewable_mime_type')) {
    function previewable_mime_type($mimeType)
    {
        return str($mimeType)->startsWith([
            'image/',
            'video/',
            'audio/',
            'text/',
            'application/pdf'
        ]);
    }
}
