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
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ArrayAdapter;
import android.widget.ScrollView;
import android.widget.Spinner;
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
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Set;
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
    private WebView publicMap;
    private boolean publicMapVisible;

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
            showPublicNearby();
        } else {
            showLoading("Restoring your session…");
            get("/me", response -> {
                JSONObject user = response.optJSONObject("user");
                permissions = userPermissions(user);
                loadDashboard();
            }, exception -> {
                showMessage(exception.getMessage());
                showPublicNearby();
            });
        }
    }

    @Override
    protected void onDestroy() {
        if (publicMap != null) {
            publicMap.destroy();
            publicMap = null;
        }
        executor.shutdownNow();
        super.onDestroy();
    }

    @Override
    public void onBackPressed() {
        if (publicMapVisible && publicMap != null && publicMap.canGoBack()) {
            publicMap.goBack();
            return;
        }
        super.onBackPressed();
    }

    private void showPublicNearby() {
        publicMapVisible = true;
        destroyPublicMap();
        LinearLayout screen = new LinearLayout(this);
        screen.setOrientation(LinearLayout.VERTICAL);
        screen.setBackgroundColor(BACKGROUND);

        LinearLayout header = card();
        header.setPadding(dp(18), dp(12), dp(18), dp(12));
        addText(header, "Farmers near Sagay City", 21, DARK, true);
        addText(header, "Browse farm locations in Sagay City. Tap a pin marker to view farmer details.", 14, 0xFF526577, false);
        Button login = button("Login");
        header.addView(login, matchWidth());
        login.setOnClickListener(view -> showLogin());
        screen.addView(header, matchWidth());

        publicMap = new WebView(this);
        WebSettings settings = publicMap.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setAllowContentAccess(false);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setUserAgentString(
                "SwineLocateAndroid/" + BuildConfig.VERSION_NAME + " (+" + applicationBaseUrl() + ")");
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        publicMap.setWebViewClient(new WebViewClient());
        screen.addView(publicMap, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                0,
                1.0f));
        setContentView(screen);

        publicMap.loadDataWithBaseURL(applicationBaseUrl(), generateSagayCityMapHtml(new JSONArray()), "text/html", "UTF-8", null);

        get("/public/farmers-nearby", response -> {
            JSONArray data = response.optJSONArray("data");
            if (publicMap != null) {
                publicMap.loadDataWithBaseURL(
                        applicationBaseUrl(),
                        generateSagayCityMapHtml(data == null ? new JSONArray() : data),
                        "text/html",
                        "UTF-8",
                        null);
            }
        }, exception -> {
            showMessage("Unable to load Sagay farmer locations: " + exception.getMessage());
        });
    }

    private void showLogin() {
        publicMapVisible = false;
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(24), dp(24), dp(24), dp(24));
        panel.setGravity(Gravity.CENTER_HORIZONTAL);
        Button back = button("‹  Farmers near Sagay City");
        panel.addView(back, matchWidth());
        back.setOnClickListener(view -> showPublicNearby());
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
        publicMapVisible = false;
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
                open.setOnClickListener(view -> {
                    if ("register-swine".equals(resource)) {
                        createResource("swine");
                    } else {
                        showResource(resource, 1, "");
                    }
                });
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
        if (canCreate(resource)) {
            Button addButton = button("Add " + label(resource));
            panel.addView(addButton, matchWidth());
            addButton.setOnClickListener(view -> createResource(resource));
        }
        if ("farms".equals(resource) || "farmers".equals(resource) || "locations".equals(resource)) {
            Button mapButton = button("View Sagay City Map  ›");
            panel.addView(mapButton, matchWidth());
            mapButton.setOnClickListener(view -> showFarmersMap(resource));
        }
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
                        addRecord(resource, panel, record);
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

    private void addRecord(String resource, LinearLayout panel, JSONObject record) {
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
        row.setOnClickListener(view -> {
            AlertDialog.Builder dialog = new AlertDialog.Builder(this)
                    .setTitle(title.isEmpty() ? "Record details" : title)
                    .setMessage(pretty(record))
                    .setPositiveButton("Close", null);
            if (canEdit(resource)) {
                dialog.setNeutralButton("Edit", (d, which) -> openForm(resource, record));
            }
            if (canDelete(resource)) {
                dialog.setNegativeButton("Delete", (d, which) -> confirmDelete(resource, record, title));
            }
            dialog.show();
        });
    }

    private void confirmDelete(String resource, JSONObject record, String title) {
        new AlertDialog.Builder(this)
                .setTitle("Confirm")
                .setMessage(("farms".equals(resource) || "locations".equals(resource) || "swine".equals(resource)
                        ? "Deactivate " : "Delete ") + (title.isEmpty() ? "this record" : title) + "?")
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Yes", (d, which) -> send("DELETE",
                        "/data/" + resource + "/" + record.optString("id"), null, response -> {
                            showMessage("Done.");
                            showResource(resource, 1, "");
                        }, exception -> showMessage(exception.getMessage())))
                .show();
    }

    private boolean canEdit(String resource) {
        switch (resource) {
            case "farms": return hasPermission("manage-farms");
            case "farmers": return hasPermission("manage-farmers");
            case "locations": return hasPermission("manage-locations");
            case "users": return hasPermission("manage-users");
            case "roles": return hasPermission("manage-roles");
            case "swine": return hasPermission("manage-swine");
            case "health-records":
            case "vaccinations": return hasPermission("manage-health");
            case "weight-records": return hasPermission("record-weight");
            default: return false;
        }
    }

    private boolean canDelete(String resource) {
        return !"roles".equals(resource) && !"farmers".equals(resource) && canEdit(resource);
    }

    private boolean canCreate(String resource) {
        switch (resource) {
            case "farms": return hasPermission("manage-farms");
            case "farmers": return hasPermission("manage-farmers");
            case "locations": return hasPermission("manage-locations");
            case "users": return hasPermission("manage-users");
            default: break;
        }
        if ("swine".equals(resource)) {
            return hasPermission("register-swine");
        }
        if ("health-records".equals(resource) || "vaccinations".equals(resource)) {
            return hasPermission("manage-health");
        }
        if ("weight-records".equals(resource)) {
            return hasPermission("record-weight");
        }
        return "movements".equals(resource) && hasPermission("manage-movements");
    }

    private void createResource(String resource) {
        openForm(resource, null);
    }

    private void openForm(String resource, JSONObject existing) {
        if (existing == null ? !canCreate(resource) : !canEdit(resource)) {
            showMessage("Your account does not have permission for this action.");
            return;
        }
        if ("farms".equals(resource)) {
            showCreateForm(resource, new JSONObject(), existing);
            return;
        }
        showLoading("Loading form choices…");
        get("/options/" + resource, response -> {
            JSONObject options = response.optJSONObject("data");
            if (options == null) {
                showMessage("The server returned incomplete form choices.");
                return;
            }
            if ("roles".equals(resource)) {
                showRolePermissions(existing, options.optJSONArray("permissions"));
            } else {
                showCreateForm(resource, options, existing);
            }
        }, exception -> showMessage(exception.getMessage()));
    }

    private void showRolePermissions(JSONObject role, JSONArray all) {
        if (all == null || role == null) {
            showMessage("No permissions available.");
            showResource("roles", 1, "");
            return;
        }
        Set<String> granted = new HashSet<>();
        JSONArray current = role.optJSONArray("permissions");
        for (int index = 0; current != null && index < current.length(); index++) {
            JSONObject permission = current.optJSONObject(index);
            if (permission != null) {
                granted.add(permission.optString("slug"));
            }
        }
        String[] names = new String[all.length()];
        String[] slugs = new String[all.length()];
        boolean[] checked = new boolean[all.length()];
        for (int index = 0; index < all.length(); index++) {
            JSONObject permission = all.optJSONObject(index);
            slugs[index] = permission == null ? "" : permission.optString("slug");
            names[index] = permission == null ? "" : permission.optString("name", slugs[index]);
            checked[index] = granted.contains(slugs[index]);
        }
        showResource("roles", 1, "");
        new AlertDialog.Builder(this)
                .setTitle("Permissions: " + role.optString("name"))
                .setMultiChoiceItems(names, checked, (d, which, isChecked) -> checked[which] = isChecked)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Save", (d, which) -> {
                    JSONArray selected = new JSONArray();
                    for (int index = 0; index < slugs.length; index++) {
                        if (checked[index]) {
                            selected.put(slugs[index]);
                        }
                    }
                    JSONObject body = new JSONObject();
                    try {
                        body.put("permissions", selected);
                    } catch (JSONException exception) {
                        showMessage("Unable to prepare permissions.");
                        return;
                    }
                    send("PUT", "/data/roles/" + role.optString("id"), body, response -> {
                        showMessage("Permissions saved.");
                        showResource("roles", 1, "");
                    }, exception -> showMessage(exception.getMessage()));
                })
                .show();
    }

    private void showCreateForm(String resource, JSONObject options, JSONObject existing) {
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(20), dp(20), dp(20), dp(20));
        Button back = button("‹  Back to " + label(resource));
        panel.addView(back, matchWidth());
        back.setOnClickListener(view -> showResource(resource, 1, ""));
        addText(panel, (existing == null ? "Add " : "Edit ") + label(resource), 24, DARK, true);

        List<FormField> fields = formFields(resource);
        if (existing != null && ("health-records".equals(resource) || "vaccinations".equals(resource)
                || "weight-records".equals(resource))) {
            fields.removeIf(field -> "swine_id".equals(field.key));
        }
        Map<String, EditText> textInputs = new HashMap<>();
        Map<String, Spinner> selectors = new HashMap<>();

        for (FormField definition : fields) {
            addText(panel, label(definition.key) + (definition.required ? " *" : ""), 14, DARK, true);
            if (definition.optionsKey != null || definition.choices != null) {
                Spinner spinner = new Spinner(this);
                List<String> values = new ArrayList<>();
                List<String> labels = new ArrayList<>();
                values.add("");
                labels.add(definition.required ? "Choose " + label(definition.key) : "Not selected");

                if (definition.choices != null) {
                    for (String choice : definition.choices) {
                        values.add(choice);
                        labels.add(label(choice));
                    }
                } else {
                    JSONArray records = options.optJSONArray(definition.optionsKey);
                    if (records != null) {
                        for (int index = 0; index < records.length(); index++) {
                            JSONObject record = records.optJSONObject(index);
                            if (record != null) {
                                values.add(record.optString("id"));
                                labels.add(optionLabel(definition.optionsKey, record));
                            }
                        }
                    }
                }

                ArrayAdapter<String> adapter = new ArrayAdapter<>(
                        this,
                        android.R.layout.simple_spinner_item,
                        labels);
                adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
                spinner.setAdapter(adapter);
                if (existing != null) {
                    String current = existing.isNull(definition.key) ? "" : existing.optString(definition.key);
                    if ("1".equals(current) && "is_for_sale".equals(definition.key)) {
                        current = "true";
                    } else if ("0".equals(current) && "is_for_sale".equals(definition.key)) {
                        current = "false";
                    }
                    int position = values.indexOf(current);
                    spinner.setSelection(Math.max(position, 0));
                }
                panel.addView(spinner, matchWidth());
                selectors.put(definition.key, spinner);
                definition.values = values;
            } else {
                int inputType = definition.multiline
                        ? InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE
                        : InputType.TYPE_CLASS_TEXT;
                if (definition.numeric) {
                    inputType = InputType.TYPE_CLASS_NUMBER
                            | InputType.TYPE_NUMBER_FLAG_DECIMAL
                            | InputType.TYPE_NUMBER_FLAG_SIGNED;
                } else if (definition.date) {
                    inputType = InputType.TYPE_CLASS_DATETIME | InputType.TYPE_DATETIME_VARIATION_DATE;
                } else if ("password".equals(definition.key)) {
                    inputType = InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD;
                }
                EditText input = field(definition.hint, inputType);
                if (definition.multiline) {
                    input.setSingleLine(false);
                    input.setMinLines(2);
                }
                if (existing != null && !"password".equals(definition.key)) {
                    String current = existing.isNull(definition.key) ? "" : existing.optString(definition.key);
                    if (definition.date && current.length() > 10) {
                        current = current.substring(0, 10);
                    }
                    input.setText(current);
                } else if (definition.date && existing == null && definition.required) {
                    input.setText(today());
                }
                panel.addView(input, matchWidth());
                textInputs.put(definition.key, input);
            }
        }

        Button save = button("Save");
        panel.addView(save, matchWidth());
        addPanel(page, panel);
        setContentView(page);

        save.setOnClickListener(view -> {
            JSONObject body = new JSONObject();
            try {
                for (FormField definition : fields) {
                    String value;
                    if (selectors.containsKey(definition.key)) {
                        Spinner spinner = selectors.get(definition.key);
                        value = definition.values.get(spinner.getSelectedItemPosition());
                    } else {
                        value = textInputs.get(definition.key).getText().toString().trim();
                    }
                    if (definition.required && value.isEmpty()) {
                        showMessage("Please enter or select " + label(definition.key) + ".");
                        return;
                    }
                    if (existing != null || !value.isEmpty()) {
                        if ("is_for_sale".equals(definition.key)) {
                            body.put(definition.key, "true".equals(value));
                        } else {
                            body.put(definition.key, value);
                        }
                    }
                }
            } catch (JSONException exception) {
                showMessage("Unable to prepare the record.");
                return;
            }

            save.setEnabled(false);
            save.setText("Saving…");
            String method = existing == null ? "POST" : "PUT";
            String path = "/data/" + resource + (existing == null ? "" : "/" + existing.optString("id"));
            send(method, path, body, response -> {
                showMessage(label(resource) + " saved.");
                showResource(resource, 1, "");
            }, exception -> {
                save.setEnabled(true);
                save.setText("Save");
                showMessage(exception.getMessage());
            });
        });
    }

    private List<FormField> formFields(String resource) {
        List<FormField> fields = new ArrayList<>();
        if ("swine".equals(resource)) {
            fields.add(FormField.options("farm_id", "farms", true));
            fields.add(FormField.options("farmer_id", "farmers", true));
            fields.add(FormField.options("current_location_id", "locations", false));
            fields.add(FormField.text("tag_number", true));
            fields.add(FormField.choices("sex", true, "male", "female"));
            fields.add(FormField.text("breed", false));
            fields.add(FormField.date("birth_date", false));
            fields.add(FormField.date("acquisition_date", false));
            fields.add(FormField.text("source", false));
            fields.add(FormField.choices("is_for_sale", false, "true", "false"));
            fields.add(FormField.number("asking_price", false));
            fields.add(FormField.multiline("notes", false));
        } else if ("farms".equals(resource)) {
            fields.add(FormField.text("farm_code", true));
            fields.add(FormField.text("name", true));
            fields.add(FormField.multiline("address", false));
            fields.add(FormField.text("municipality", false));
            fields.add(FormField.text("province", false));
            fields.add(FormField.text("region", false));
            fields.add(FormField.number("latitude", false));
            fields.add(FormField.number("longitude", false));
            fields.add(FormField.text("contact_number", false));
            fields.add(FormField.text("email", false));
        } else if ("farmers".equals(resource)) {
            fields.add(FormField.options("farm_id", "farms", true));
            fields.add(FormField.text("name", true));
            fields.add(FormField.text("contact_number", true));
            fields.add(FormField.text("email", false));
            fields.add(FormField.multiline("address", true));
        } else if ("locations".equals(resource)) {
            fields.add(FormField.options("farm_id", "farms", true));
            fields.add(FormField.text("location_code", true));
            fields.add(FormField.text("name", true));
            fields.add(FormField.text("type", false));
            fields.add(FormField.multiline("description", false));
            fields.add(FormField.number("capacity", false));
        } else if ("users".equals(resource)) {
            fields.add(FormField.text("name", true));
            fields.add(FormField.text("email", true));
            fields.add(FormField.text("password", false));
            fields.add(FormField.options("role_id", "roles", true));
            fields.add(FormField.options("farm_id", "farms", false));
            fields.add(FormField.text("phone", false));
            fields.add(FormField.choices("status", true, "active", "inactive"));
        } else if ("health-records".equals(resource) || "vaccinations".equals(resource)) {
            fields.add(FormField.options("swine_id", "swine", true));
            fields.add(FormField.date("record_date", true));
            fields.add("vaccinations".equals(resource)
                    ? FormField.choices("record_type", true, "Vaccination")
                    : FormField.choices("record_type", true,
                            "Routine Examination", "Vaccination", "Illness", "Treatment",
                            "Injury", "Follow-up", "Other"));
            fields.add(FormField.text("vaccine_name", false));
            fields.add(FormField.text("dose", false));
            fields.add(FormField.text("batch_number", false));
            fields.add(FormField.date("next_due_date", false));
            fields.add(FormField.choices("health_status", true,
                    "healthy", "under_observation", "sick", "recovering"));
            fields.add(FormField.multiline("symptoms", false));
            fields.add(FormField.multiline("diagnosis", false));
            fields.add(FormField.multiline("treatment", false));
            fields.add(FormField.multiline("observations", false));
            fields.add(FormField.multiline("veterinary_assessment", false));
            fields.add(FormField.multiline("notes", false));
        } else if ("weight-records".equals(resource)) {
            fields.add(FormField.options("swine_id", "swine", true));
            fields.add(FormField.date("record_date", true));
            fields.add(FormField.number("weight", true));
            fields.add(FormField.multiline("notes", false));
        } else if ("movements".equals(resource)) {
            fields.add(FormField.options("swine_id", "swine", true));
            fields.add(FormField.options("to_farm_id", "farms", true));
            fields.add(FormField.options("to_location_id", "locations", true));
            fields.add(FormField.options("to_farmer_id", "farmers", false));
            fields.add(FormField.date("movement_date", true));
            fields.add(FormField.text("reason", false));
            fields.add(FormField.multiline("notes", false));
        }
        return fields;
    }

    private String optionLabel(String optionsKey, JSONObject option) {
        String name = firstValue(option, "name", "tag_number");
        if (name.isEmpty()) {
            name = "Record #" + option.optString("id");
        }
        if ("farms".equals(optionsKey)) {
            String code = option.optString("farm_code", "");
            return code.isEmpty() ? name : name + " (" + code + ")";
        }
        if ("locations".equals(optionsKey)) {
            String code = option.optString("location_code", "");
            return code.isEmpty() ? name : name + " (" + code + ")";
        }
        return name;
    }

    private String today() {
        return new SimpleDateFormat("yyyy-MM-dd", Locale.US).format(new Date());
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
        showPublicNearby();
    }

    private String applicationBaseUrl() {
        String apiBaseUrl = BuildConfig.API_BASE_URL;
        String apiSuffix = "/api/mobile";
        return apiBaseUrl.endsWith(apiSuffix)
                ? apiBaseUrl.substring(0, apiBaseUrl.length() - apiSuffix.length())
                : apiBaseUrl;
    }

    private void destroyPublicMap() {
        if (publicMap != null) {
            publicMap.stopLoading();
            publicMap.destroy();
            publicMap = null;
        }
    }

    private String generateSagayCityMapHtml(JSONArray items) {
        StringBuilder markersJs = new StringBuilder();
        List<String> markerPositions = new ArrayList<>();

        if (items != null) {
            for (int index = 0; index < items.length(); index++) {
                JSONObject item = items.optJSONObject(index);
                if (item == null) {
                    continue;
                }

                double latitude = item.optDouble("latitude", Double.NaN);
                double longitude = item.optDouble("longitude", Double.NaN);
                if (Double.isNaN(latitude) || Double.isInfinite(latitude)
                        || Double.isNaN(longitude) || Double.isInfinite(longitude)
                        || latitude < -90 || latitude > 90
                        || longitude < -180 || longitude > 180
                        || latitude < 10.4 || latitude > 11.35
                        || longitude < 122.9 || longitude > 123.85) {
                    continue;
                }

                String farmerName = item.optString("farmer_name", "Farmer");
                String farmName = item.optString("farm_name", "Farm");
                String profileUrl = item.optString("profile_url", "");
                int swineCount = item.optInt("swine_count", 0);
                StringBuilder popupHtml = new StringBuilder("<div class='farmer-card'><h3>")
                        .append(escapeHtml(farmerName))
                        .append("</h3><p>")
                        .append(escapeHtml(farmName))
                        .append("</p><p><span class='badge'>")
                        .append(swineCount)
                        .append(" available listing")
                        .append(swineCount == 1 ? "" : "s")
                        .append("</span></p>");
                if (!profileUrl.isEmpty()) {
                    popupHtml.append("<p><a href='")
                            .append(escapeHtml(profileUrl))
                            .append("'>View SwineLocate Farmer Profile</a></p>");
                }
                popupHtml.append("</div>");

                markersJs.append("L.marker([")
                        .append(Double.toString(latitude))
                        .append(", ")
                        .append(Double.toString(longitude))
                        .append("]).addTo(map).bindPopup(")
                        .append(JSONObject.quote(popupHtml.toString()))
                        .append(");\n");
                markerPositions.add("[" + Double.toString(latitude) + ", "
                        + Double.toString(longitude) + "]");
            }
        }

        StringBuilder fitMarkersJs = new StringBuilder();
        if (markerPositions.size() == 1) {
            fitMarkersJs.append("map.setView(").append(markerPositions.get(0)).append(", 14);\n");
        } else if (markerPositions.size() > 1) {
            fitMarkersJs.append("map.fitBounds([");
            for (int index = 0; index < markerPositions.size(); index++) {
                if (index > 0) {
                    fitMarkersJs.append(",");
                }
                fitMarkersJs.append(markerPositions.get(index));
            }
            fitMarkersJs.append("], { padding: [24, 24], maxZoom: 14 });\n");
        }

        return "<!DOCTYPE html>\n" +
                "<html>\n" +
                "<head>\n" +
                "  <meta charset='utf-8' />\n" +
                "  <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />\n" +
                "  <link rel='stylesheet' href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' />\n" +
                "  <script src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'></script>\n" +
                "  <style>\n" +
                "    html, body, #map { height: 100%; width: 100%; margin: 0; padding: 0; background: #f4f7fa; }\n" +
                "    .farmer-card { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 13px; color: #18324B; line-height: 1.4; }\n" +
                "    .farmer-card h3 { margin: 0 0 6px 0; color: #3368A0; font-size: 16px; font-weight: bold; }\n" +
                "    .farmer-card p { margin: 3px 0; }\n" +
                "    .badge { display: inline-block; background-color: #3368A0; color: white; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-top: 4px; }\n" +
                "  </style>\n" +
                "</head>\n" +
                "<body>\n" +
                "  <div id='map'></div>\n" +
                              "  <script>\n" +
                "    var map = L.map('map').setView([10.8942, 123.4293], 12);\n" +
                "    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {\n" +
                "      maxZoom: 19,\n" +
                "      attribution: '&copy; OpenStreetMap contributors | SwineLocate'\n" +
                "    }).addTo(map);\n" +
                markersJs.toString() +
                fitMarkersJs +
                "  </script>\n" +
                "</body>\n" +
                "</html>";
    }

    private String escapeHtml(String value) {
        return value.replace("&", "&amp;")
                .replace("<", "&lt;")
                .replace(">", "&gt;")
                .replace("\"", "&quot;")
                .replace("'", "&#039;");
    }

    private void showFarmersMap(String resource) {
        publicMapVisible = true;
        destroyPublicMap();
        LinearLayout screen = new LinearLayout(this);
        screen.setOrientation(LinearLayout.VERTICAL);
        screen.setBackgroundColor(BACKGROUND);

        LinearLayout header = card();
        header.setPadding(dp(18), dp(12), dp(18), dp(12));
        Button back = button("‹  Back to " + label(resource));
        header.addView(back, matchWidth());
        back.setOnClickListener(view -> showResource(resource, 1, ""));

        addText(header, "Farmers Map (Sagay City)", 21, DARK, true);
        addText(header, "Showing farm locations in Sagay City. Tap a pin marker to view details.", 14, 0xFF526577, false);
        screen.addView(header, matchWidth());

        publicMap = new WebView(this);
        WebSettings settings = publicMap.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setAllowContentAccess(false);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setUserAgentString(
                "SwineLocateAndroid/" + BuildConfig.VERSION_NAME + " (https://swinelocate.com; support@swinelocate.com)");
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        publicMap.setWebViewClient(new WebViewClient());
        screen.addView(publicMap, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                0,
                1.0f));
        setContentView(screen);

        publicMap.loadDataWithBaseURL(applicationBaseUrl(), generateSagayCityMapHtml(new JSONArray()), "text/html", "UTF-8", null);

        get("/public/farmers-nearby", response -> {
            JSONArray data = response.optJSONArray("data");
            if (publicMap != null) {
                publicMap.loadDataWithBaseURL(
                        applicationBaseUrl(),
                        generateSagayCityMapHtml(data == null ? new JSONArray() : data),
                        "text/html",
                        "UTF-8",
                        null);
            }
        }, exception -> showMessage(exception.getMessage()));
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

    private void send(String method, String path, JSONObject body, Callback callback, ErrorCallback errorCallback) {
        executor.execute(() -> {
            try {
                JSONObject result;
                if ("PUT".equals(method)) {
                    result = api.put(path, body, token);
                } else if ("DELETE".equals(method)) {
                    result = api.delete(path, token);
                } else {
                    result = api.post(path, body, token);
                }
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

    private static final class FormField {
        final String key;
        final String optionsKey;
        final boolean required;
        final boolean multiline;
        final boolean numeric;
        final boolean date;
        final String hint;
        final String[] choices;
        List<String> values = new ArrayList<>();

        private FormField(
                String key,
                String optionsKey,
                boolean required,
                boolean multiline,
                boolean numeric,
                boolean date,
                String hint,
                String[] choices) {
            this.key = key;
            this.optionsKey = optionsKey;
            this.required = required;
            this.multiline = multiline;
            this.numeric = numeric;
            this.date = date;
            this.hint = hint;
            this.choices = choices;
        }

        static FormField options(String key, String optionsKey, boolean required) {
            return new FormField(key, optionsKey, required, false, false, false, "", null);
        }

        static FormField choices(String key, boolean required, String... choices) {
            return new FormField(key, null, required, false, false, false, "", choices);
        }

        static FormField text(String key, boolean required) {
            return new FormField(key, null, required, false, false, false, labelHint(key), null);
        }

        static FormField multiline(String key, boolean required) {
            return new FormField(key, null, required, true, false, false, labelHint(key), null);
        }

        static FormField number(String key, boolean required) {
            return new FormField(key, null, required, false, true, false, labelHint(key), null);
        }

        static FormField date(String key, boolean required) {
            return new FormField(key, null, required, false, false, true, "YYYY-MM-DD", null);
        }

        private static String labelHint(String key) {
            return key.replace('_', ' ');
        }
    }
}
