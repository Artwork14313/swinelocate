package com.swine.locate;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;

final class MobileApi {
    JSONObject get(String path, String token) throws IOException {
        return request("GET", path, null, token);
    }

    JSONObject post(String path, JSONObject body, String token) throws IOException {
        return request("POST", path, body, token);
    }

    private JSONObject request(String method, String path, JSONObject body, String token) throws IOException {
        HttpURLConnection connection = null;
        try {
            URL url = new URL(BuildConfig.API_BASE_URL + path);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod(method);
            connection.setConnectTimeout(15000);
            connection.setReadTimeout(20000);
            connection.setRequestProperty("Accept", "application/json");
            if (token != null && !token.isEmpty()) {
                connection.setRequestProperty("Authorization", "Bearer " + token);
            }
            if (body != null) {
                connection.setDoOutput(true);
                connection.setRequestProperty("Content-Type", "application/json; charset=utf-8");
                try (OutputStream output = connection.getOutputStream()) {
                    output.write(body.toString().getBytes(StandardCharsets.UTF_8));
                }
            }

            int status = connection.getResponseCode();
            InputStream stream = status >= 200 && status < 300
                    ? connection.getInputStream()
                    : connection.getErrorStream();
            String response = readBody(stream);
            if (status < 200 || status >= 300) {
                throw new IOException(errorMessage(status, response));
            }
            return response.isEmpty() ? new JSONObject() : new JSONObject(response);
        } catch (org.json.JSONException exception) {
            throw new IOException("The server returned an invalid response.", exception);
        } finally {
            if (connection != null) {
                connection.disconnect();
            }
        }
    }

    private String readBody(InputStream stream) throws IOException {
        if (stream == null) {
            return "";
        }
        StringBuilder result = new StringBuilder();
        try (BufferedReader reader = new BufferedReader(
                new InputStreamReader(stream, StandardCharsets.UTF_8))) {
            String line;
            while ((line = reader.readLine()) != null) {
                result.append(line);
            }
        }
        return result.toString();
    }

    private String errorMessage(int status, String body) {
        try {
            JSONObject json = new JSONObject(body);
            JSONObject errors = json.optJSONObject("errors");
            if (errors != null) {
                java.util.Iterator<String> keys = errors.keys();
                if (keys.hasNext()) {
                    org.json.JSONArray messages = errors.optJSONArray(keys.next());
                    if (messages != null && messages.length() > 0) {
                        return messages.optString(0);
                    }
                }
            }
            String message = json.optString("message");
            if (!message.isEmpty()) {
                return message;
            }
        } catch (org.json.JSONException ignored) {
            // A status-based message is more useful than exposing an unparseable server response.
        }
        return "Request failed (" + status + "). Please try again.";
    }
}
