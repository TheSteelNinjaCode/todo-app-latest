<?php

/**
 * Redirects the user to the specified URL.
 *
 * @param string $url The URL to redirect to.
 * @param bool $replace Optional. Whether to replace the previous header. Default is true.
 * @param int $responseCode Optional. The HTTP response status code. Default is 0 (no status code).
 * @return void
 */
function redirect(string $url, bool $replace = true, int $responseCode = 0)
{
    global $isWire, $isAjax;

    echo "redirect_7F834=$url";
    if (!$isWire && !$isAjax)
        header("Location: $url", $replace, $responseCode);
    exit;
}

function isAjaxRequest()
{
    $isAjax = false;

    // Check for standard AJAX header
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $isAjax = true;
    }

    // Check for common AJAX content types
    if (!empty($_SERVER['CONTENT_TYPE'])) {
        $ajaxContentTypes = [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ];

        foreach ($ajaxContentTypes as $contentType) {
            if (strpos($_SERVER['CONTENT_TYPE'], $contentType) !== false) {
                $isAjax = true;
                break;
            }
        }
    }

    // Check for common AJAX request methods
    $ajaxMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    if (in_array(strtoupper($_SERVER['REQUEST_METHOD']), $ajaxMethods)) {
        $isAjax = true;
    }

    return $isAjax;
}

function isWireRequest(): bool
{
    $serverFetchSite = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
    if (isset($serverFetchSite) && $serverFetchSite === 'same-origin') {
        $headers = getallheaders();
        return isset($headers['http_pphp_wire_request']) && strtolower($headers['http_pphp_wire_request']) === 'true';
    }

    return false;
}
