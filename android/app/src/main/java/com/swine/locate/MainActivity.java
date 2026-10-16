package com.swine.locate;

import android.app.Activity;
import android.app.AlertDialog;
import android.graphics.Typeface;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.InputType;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;
import android.widget.Toast;

import com.google.mlkit.vision.codescanner.GmsBarcodeScanning;
import com.google.mlkit.vision.codescanner.GmsBarcodeScannerOptions;
import com.google.mlkit.vision.barcode.common.Barcode;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.Iterator;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public final class MainActivity extends Activity {
    private static final int BLUE = 0xFF3368A0;
    private static final int DARK = 0xFF18324B;
    private static final int BACKGROUND = 0xFFF4F7FA;
    private static final int CARD = 0xFFFFFFFF;

    private final MobileApi api = new MobileApi();
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
    private TokenStore tokenStore;
    private String token;
    private JSONArray permissions = new JSONArray();
    private JSONObject dashboard;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        tokenStore = new TokenStore(this);
        try {
            token = tokenStore.read();
        } catch (Exception exception) {
            tokenStore.clear();
            showMessage(exception.getMessage());
        }

        if (token == null || token.isEmpty()) {
            showLogin();
        } else {
            showLoading("Restoring your session…");
            get("/me", response -> {
                JSONObject user = response.optJSONObject("user");
                permissions = userPermissions(user);
                loadDashboard();
            }, exception -> {
                showMessage(exception.getMessage());
                showLogin();
            });
        }
    }

    @Override
    protected void onDestroy() {
        executor.shutdownNow();
        super.onDestroy();
    }

    private void showLogin() {
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(24), dp(24), dp(24), dp(24));
        panel.setGravity(Gravity.CENTER_HORIZONTAL);
        addText(panel, "SwineLocate", 30, DARK, true);
        addText(panel, "Sign in to manage your swine records.", 15, 0xFF526577, false);
        EditText email = field("Email address", InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
        EditText password = field("Password", InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
        panel.addView(email, matchWidth());
        panel.addView(password, matchWidth());
        Button signIn = button("Sign in");
        panel.addView(signIn, matchWidth());
        addPanel(page, panel);
        setContentView(page);

        signIn.setOnClickListener(view -> {
            String address = email.getText().toString().trim();
            String secret = password.getText().toString();
            if (address.isEmpty() || secret.isEmpty()) {
                showMessage("Enter your email and password.");
                return;
            }
            signIn.setEnabled(false);
            signIn.setText("Signing in…");
            JSONObject body = new JSONObject();
            try {
                body.put("email", address);
                body.put("password", secret);
            } catch (JSONException exception) {
                signIn.setEnabled(true);
                signIn.setText("Sign in");
                showMessage("Unable to prepare sign-in request.");
                return;
            }
            post("/login", body, null, response -> {
                try {
                    token = response.getString("token");
                    tokenStore.save(token);
                    JSONObject user = response.getJSONObject("user");
                    permissions = user.optJSONArray("permissions");
                    loadDashboard();
                } catch (Exception exception) {
                    signIn.setEnabled(true);
                    signIn.setText("Sign in");
                    showMessage("Could not save the secure sign-in session.");
                }
            }, exception -> {
                signIn.setEnabled(true);
                signIn.setText("Sign in");
                showMessage(exception.getMessage());
            });
        });
    }

    private void loadDashboard() {
        showLoading("Loading your dashboard…");
        get("/dashboard", response -> {
            dashboard = response.optJSONObject("data");
            if (dashboard == null) {
                showDashboardFailure("The server returned an incomplete dashboard.");
                return;
            }
            renderDashboard();
        }, exception -> showDashboardFailure(exception.getMessage()));
    }

    private void renderDashboard() {
        JSONObject user = dashboard.optJSONObject("user");
        permissions = userPermissions(user);
        JSONObject stats = dashboard.optJSONObject("stats");
        JSONArray modules = dashboard.optJSONArray("modules");
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(20), dp(20), dp(20), dp(20));
        String name = user == null ? "Welcome" : user.optString("name", "Welcome");
        addText(panel, "Hello, " + name, 25, DARK, true);
        String role = user == null ? "" : user.optString("role", "");
        String farm = user == null ? "" : user.optString("farm", "");
        addText(panel, role + (farm.isEmpty() ? "" : " · " + farm), 14, 0xFF526577, false);
        if (stats != null && stats.length() > 0) {
            addText(panel, "Overview", 18, DARK, true);
            Iterator<String> keys = stats.keys();
            while (keys.hasNext()) {
                String key = keys.next();
                addText(panel, label(key) + ": " + stats.opt(key), 15, 0xFF283C50, false);
            }
        }
        if (hasPermission("scan-qr")) {
            Button scanner = button("Scan swine QR code");
            panel.addView(scanner, matchWidth());
            scanner.setOnClickListener(view -> scanQr());
        }
        addText(panel, "Your modules", 18, DARK, true);
        if (modules != null) {
            for (int index = 0; index < modules.length(); index++) {
                JSONObject module = modules.optJSONObject(index);
                if (module == null) {
                    continue;
                }
                String resource = module.optString("id");
                Button open = button(module.optString("label", label(resource)) + "  ›");
                panel.addView(open, matchWidth());
                open.setOnClickListener(view -> showResource(resource, 1, ""));
            }
        }
        Button signOut = button("Sign out");
        panel.addView(signOut, matchWidth());
        signOut.setOnClickListener(view -> signOut());
        addPanel(page, panel);
        setContentView(page);
    }

    private void showResource(String resource, int pageNumber, String search) {
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(20), dp(20), dp(20), dp(20));
        Button back = button("‹  Dashboard");
        panel.addView(back, matchWidth());
        back.setOnClickListener(view -> renderDashboard());
        addText(panel, label(resource), 24, DARK, true);
        EditText filter = field("Search records", InputType.TYPE_CLASS_TEXT);
        filter.setText(search);
        panel.addView(filter, matchWidth());
        Button searchButton = button("Search");
        panel.addView(searchButton, matchWidth());
        TextView results = addText(panel, "Loading records…", 14, 0xFF526577, false);
        addPanel(page, panel);
        setContentView(page);

        searchButton.setOnClickListener(view ->
                showResource(resource, 1, filter.getText().toString().trim()));

        String path = "/data/" + resource + "?page=" + pageNumber;
        if (!search.isEmpty()) {
            try {
                path += "&search=" + URLEncoder.encode(search, StandardCharsets.UTF_8.name());
            } catch (java.io.UnsupportedEncodingException exception) {
                showMessage("Unable to encode the search term.");
                return;
            }
        }
        get(path, response -> {
            if ("reports".equals(resource)) {
                JSONObject data = response.optJSONObject("data");
                results.setText(data == null ? "No report data." : pretty(data));
                results.setTextColor(DARK);
                return;
            }
            JSONArray rows = response.optJSONArray("data");
            if (rows == null || rows.length() == 0) {
                results.setText("No records found.");
            } else {
                panel.removeView(results);
                for (int index = 0; index < rows.length(); index++) {
                    JSONObject record = rows.optJSONObject(index);
                    if (record != null) {
                        addRecord(panel, record);
                    }
                }
            }
            JSONObject meta = response.optJSONObject("meta");
            if (meta != null && meta.optInt("current_page") < meta.optInt("last_page")) {
                Button next = button("Next page");
                panel.addView(next, matchWidth());
                next.setOnClickListener(view ->
                        showResource(resource, pageNumber + 1, search));
            }
        }, exception -> results.setText(exception.getMessage()));
    }

    private void addRecord(LinearLayout panel, JSONObject record) {
        LinearLayout row = new LinearLayout(this);
        row.setOrientation(LinearLayout.VERTICAL);
        row.setPadding(dp(14), dp(12), dp(14), dp(12));
        row.setBackgroundColor(0xFFF2F6FA);
        String title = firstValue(record, "tag_number", "name", "farm_code", "location_code", "record_type", "reason");
        String detail = firstValue(record, "status", "record_date", "movement_date", "email", "breed");
        addText(row, title.isEmpty() ? "Record #" + record.optString("id") : title, 16, DARK, true);
        if (!detail.isEmpty()) {
            addText(row, label(detail), 13, 0xFF526577, false);
        }
        LinearLayout.LayoutParams params = matchWidth();
        params.bottomMargin = dp(8);
        panel.addView(row, params);
        row.setOnClickListener(view -> new AlertDialog.Builder(this)
                .setTitle(title.isEmpty() ? "Record details" : title)
                .setMessage(pretty(record))
                .setPositiveButton("Close", null)
                .show());
    }

    private void scanQr() {
        GmsBarcodeScannerOptions options = new GmsBarcodeScannerOptions.Builder()
                .setBarcodeFormats(Barcode.FORMAT_QR_CODE)
                .build();
        GmsBarcodeScanning.getClient(this, options)
                .startScan()
                .addOnSuccessListener(barcode -> {
                    String value = barcode.getRawValue();
                    if (value == null || value.trim().isEmpty()) {
                        showMessage("The QR code did not contain a swine token.");
                        return;
                    }
                    lookupQr(value.trim());
                })
                .addOnFailureListener(exception -> showMessage(exception.getMessage()))
                .addOnCanceledListener(() -> { });
    }

    private void lookupQr(String value) {
        try {
            Uri scannedUri = Uri.parse(value);
            if (scannedUri.getScheme() != null) {
                String scannedHost = scannedUri.getHost();
                String apiHost = Uri.parse(BuildConfig.API_BASE_URL).getHost();
                if (scannedHost == null || !scannedHost.equalsIgnoreCase(apiHost)) {
                    showMessage("This QR code is not from SwineLocate.");
                    return;
                }
                value = scannedUri.getLastPathSegment();
                if (value == null || value.isEmpty()) {
                    showMessage("This QR code does not contain a swine token.");
                    return;
                }
            }
            String encoded = URLEncoder.encode(value, StandardCharsets.UTF_8.name());
            get("/qr/" + encoded, response -> {
                JSONObject swine = response.optJSONObject("data");
                if (swine == null) {
                    showMessage("No swine record found for this QR code.");
                    return;
                }
                new AlertDialog.Builder(this)
                        .setTitle(firstValue(swine, "tag_number", "name"))
                        .setMessage(pretty(swine))
                        .setPositiveButton("Close", null)
                        .show();
            }, exception -> showMessage(exception.getMessage()));
        } catch (java.io.UnsupportedEncodingException exception) {
            showMessage("Unable to read the QR code.");
        }
    }

    private void signOut() {
        post("/logout", new JSONObject(), token, response -> signOutLocally(), exception -> {
            signOutLocally();
            showMessage(exception.getMessage());
        });
    }

    private void signOutLocally() {
        tokenStore.clear();
        token = null;
        permissions = new JSONArray();
        dashboard = null;
        showLogin();
    }

    private void get(String path, Callback callback, ErrorCallback errorCallback) {
        executor.execute(() -> {
            try {
                JSONObject result = api.get(path, token);
                mainHandler.post(() -> callback.complete(result));
            } catch (Exception exception) {
                mainHandler.post(() -> errorCallback.failed(exception));
            }
        });
    }

    private void post(String path, JSONObject body, String bearer, Callback callback, ErrorCallback errorCallback) {
        executor.execute(() -> {
            try {
                JSONObject result = api.post(path, body, bearer);
                mainHandler.post(() -> callback.complete(result));
            } catch (Exception exception) {
                mainHandler.post(() -> errorCallback.failed(exception));
            }
        });
    }

    private void addPanel(ScrollView page, View panel) {
        ((ViewGroup) page.getChildAt(0)).addView(panel);
    }

    private ScrollView page() {
        ScrollView scroll = new ScrollView(this);
        scroll.setFillViewport(true);
        scroll.setBackgroundColor(BACKGROUND);
        LinearLayout pageContent = new LinearLayout(this);
        pageContent.setOrientation(LinearLayout.VERTICAL);
        pageContent.setPadding(dp(16), dp(24), dp(16), dp(24));
        scroll.addView(pageContent);
        return scroll;
    }

    private LinearLayout card() {
        LinearLayout panel = new LinearLayout(this);
        panel.setOrientation(LinearLayout.VERTICAL);
        panel.setBackgroundColor(CARD);
        panel.setPadding(dp(18), dp(18), dp(18), dp(18));
        return panel;
    }

    private TextView addText(LinearLayout parent, String value, int size, int color, boolean bold) {
        TextView text = new TextView(this);
        text.setText(value);
        text.setTextColor(color);
        text.setTextSize(size);
        if (bold) {
            text.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        }
        text.setPadding(0, dp(5), 0, dp(5));
        parent.addView(text, matchWidth());
        return text;
    }

    private EditText field(String hint, int inputType) {
        EditText field = new EditText(this);
        field.setSingleLine(true);
        field.setHint(hint);
        field.setInputType(inputType);
        field.setTextSize(16);
        field.setPadding(dp(12), dp(10), dp(12), dp(10));
        return field;
    }

    private Button button(String title) {
        Button button = new Button(this);
        button.setText(title);
        button.setTextColor(BLUE);
        button.setAllCaps(false);
        return button;
    }

    private void showLoading(String message) {
        ScrollView page = page();
        LinearLayout panel = card();
        addText(panel, message, 18, DARK, true);
        addPanel(page, panel);
        setContentView(page);
    }

    private void showDashboardFailure(String message) {
        ScrollView page = page();
        LinearLayout panel = card();
        addText(panel, "Unable to load the dashboard", 20, DARK, true);
        addText(panel, message, 14, 0xFF526577, false);
        Button retry = button("Try again");
        panel.addView(retry, matchWidth());
        retry.setOnClickListener(view -> loadDashboard());
        Button signOut = button("Sign out");
        panel.addView(signOut, matchWidth());
        signOut.setOnClickListener(view -> signOut());
        addPanel(page, panel);
        setContentView(page);
    }

    private void showMessage(String message) {
        if (message == null || message.trim().isEmpty()) {
            message = "Something went wrong. Please try again.";
        }
        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
    }

    private boolean hasPermission(String permission) {
        for (int index = 0; index < permissions.length(); index++) {
            if (permission.equals(permissions.optString(index))) {
                return true;
            }
        }
        return false;
    }

    private JSONArray userPermissions(JSONObject user) {
        JSONArray userPermissions = user == null ? null : user.optJSONArray("permissions");
        return userPermissions == null ? new JSONArray() : userPermissions;
    }

    private String firstValue(JSONObject object, String... keys) {
        for (String key : keys) {
            String value = object.optString(key, "");
            if (!value.isEmpty() && !"null".equals(value)) {
                return value;
            }
        }
        return "";
    }

    private String pretty(JSONObject object) {
        try {
            return object.toString(2);
        } catch (JSONException exception) {
            return object.toString();
        }
    }

    private String label(String value) {
        if (value == null || value.isEmpty()) {
            return "";
        }
        String[] parts = value.replace('_', ' ').replace('-', ' ').split(" ");
        StringBuilder label = new StringBuilder();
        for (String part : parts) {
            if (part.isEmpty()) {
                continue;
            }
            if (label.length() > 0) {
                label.append(' ');
            }
            label.append(Character.toUpperCase(part.charAt(0)))
                    .append(part.substring(1));
        }
        return label.toString();
    }

    private LinearLayout.LayoutParams matchWidth() {
        return new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT);
    }

    private int dp(int value) {
        return Math.round(value * getResources().getDisplayMetrics().density);
    }

    private interface Callback {
        void complete(JSONObject response);
    }

    private interface ErrorCallback {
        void failed(Exception exception);
    }
}
