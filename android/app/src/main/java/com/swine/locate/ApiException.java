package com.swine.locate;

import java.io.IOException;

/** The server was reached and answered with an error, so retrying offline will not help. */
final class ApiException extends IOException {
    final int status;

    ApiException(int status, String message) {
        super(message);
        this.status = status;
    }

    boolean isRetryable() {
        return status >= 500 || status == 429 || status == 408;
    }
}
