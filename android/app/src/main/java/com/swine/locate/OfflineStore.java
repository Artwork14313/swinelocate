package com.swine.locate;

import android.content.Context;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.List;

/** App-private storage for cached GET responses and the queue of writes made while offline. */
final class OfflineStore {
    static final class PendingChange {
        final String key;
        final String method;
        final String path;
        final JSONObject body;

        PendingChange(String key, String method, String path, JSONObject body) {
            this.key = key;
            this.method = method;
            this.path = path;
            this.body = body;
        }
    }

    private final File cacheFile;
    private final File queueFile;
    private JSONObject cache;
    private JSONArray queue;

    OfflineStore(Context context) {
        cacheFile = new File(context.getFilesDir(), "offline-cache.json");
        queueFile = new File(context.getFilesDir(), "offline-queue.json");
        cache = readObject(cacheFile);
        queue = readArray(queueFile);
    }

    synchronized void cacheResponse(String path, JSONObject response) {
        try {
            cache.put(path, response);
            write(cacheFile, cache.toString());
        } catch (JSONException | IOException ignored) {
            // Caching is best effort; the live response has already been delivered.
        }
    }

    synchronized JSONObject cachedResponse(String path) {
        return cache.optJSONObject(path);
    }

    synchronized void enqueue(PendingChange change) throws IOException {
        try {
            JSONObject item = new JSONObject();
            item.put("key", change.key);
            item.put("method", change.method);
            item.put("path", change.path);
            item.put("body", change.body == null ? JSONObject.NULL : change.body);
            queue.put(item);
        } catch (JSONException exception) {
            throw new IOException(exception);
        }
        write(queueFile, queue.toString());
    }

    synchronized List<PendingChange> pending() {
        List<PendingChange> changes = new ArrayList<>();
        for (int index = 0; index < queue.length(); index++) {
            JSONObject item = queue.optJSONObject(index);
            if (item != null) {
                changes.add(new PendingChange(
                        item.optString("key"),
                        item.optString("method"),
                        item.optString("path"),
                        item.optJSONObject("body")));
            }
        }
        return changes;
    }

    synchronized int pendingCount() {
        return queue.length();
    }

    synchronized void remove(String key) {
        JSONArray remaining = new JSONArray();
        for (int index = 0; index < queue.length(); index++) {
            JSONObject item = queue.optJSONObject(index);
            if (item != null && !key.equals(item.optString("key"))) {
                remaining.put(item);
            }
        }
        queue = remaining;
        try {
            write(queueFile, queue.toString());
        } catch (IOException ignored) {
            // The change will be retried and the server de-duplicates by key.
        }
    }

    synchronized void clear() {
        cache = new JSONObject();
        queue = new JSONArray();
        cacheFile.delete();
        queueFile.delete();
    }

    private static void write(File file, String content) throws IOException {
        File temp = new File(file.getParentFile(), file.getName() + ".tmp");
        try (FileOutputStream output = new FileOutputStream(temp)) {
            output.write(content.getBytes(StandardCharsets.UTF_8));
        }
        if (!temp.renameTo(file)) {
            try (FileOutputStream output = new FileOutputStream(file)) {
                output.write(content.getBytes(StandardCharsets.UTF_8));
            }
            temp.delete();
        }
    }

    private static String read(File file) {
        if (!file.exists()) {
            return "";
        }
        try (FileInputStream input = new FileInputStream(file);
             ByteArrayOutputStream buffer = new ByteArrayOutputStream()) {
            byte[] chunk = new byte[8192];
            int count;
            while ((count = input.read(chunk)) != -1) {
                buffer.write(chunk, 0, count);
            }
            return new String(buffer.toByteArray(), StandardCharsets.UTF_8);
        } catch (IOException exception) {
            return "";
        }
    }

    private static JSONObject readObject(File file) {
        try {
            return new JSONObject(read(file));
        } catch (JSONException exception) {
            return new JSONObject();
        }
    }

    private static JSONArray readArray(File file) {
        try {
            return new JSONArray(read(file));
        } catch (JSONException exception) {
            return new JSONArray();
        }
    }
}
