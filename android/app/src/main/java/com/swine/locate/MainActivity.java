package com.swine.locate;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Intent;
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
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.ArrayAdapter;
import android.widget.ScrollView;
import android.widget.AdapterView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import com.google.mlkit.vision.codescanner.GmsBarcodeScanning;
import com.google.mlkit.vision.codescanner.GmsBarcodeScannerOptions;
import com.google.mlkit.vision.barcode.common.Barcode;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
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
import java.util.UUID;
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
    private final Runnable syncRunnable = () -> syncPending(false);
    private OfflineStore offlineStore;
    private volatile boolean syncing;
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
        offlineStore = new OfflineStore(this);
        try {
            token = tokenStore.read();
        } catch (Exception exception) {
            tokenStore.clear();
            showMessage(exception.getMessage());
        }

        Uri launchUri = getIntent() == null ? null : getIntent().getData();
        pendingFarmerToken = farmerTokenFromUrl(launchUri);
        if (isSwineScanUrl(launchUri)) {
            pendingSwineUrl = launchUri.toString();
        }

        if (token == null || token.isEmpty()) {
            if (pendingSwineUrl != null) {
                showMessage("Sign in to view this swine.");
                showLogin();
            } else if (pendingFarmerToken != null) {
                openPendingFarmerProfile();
            } else {
                showPublicNearby();
            }
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
    protected void onResume() {
        super.onResume();
        syncPending(false);
    }

    @Override
    protected void onDestroy() {
        mainHandler.removeCallbacks(syncRunnable);
        if (publicMap != null) {
            publicMap.destroy();
            publicMap = null;
        }
        executor.shutdownNow();
        super.onDestroy();
    }

    @Override
    public void onBackPressed() {
        if (drawerPanel != null && drawerOpen) {
            closeDrawer();
            return;
        }
        if (profileBack != null) {
            profileBack.run();
            return;
        }
        if (publicMapVisible && publicMap != null && publicMap.canGoBack()) {
            publicMap.goBack();
            return;
        }
        super.onBackPressed();
    }

    private View drawerScrim;
    private LinearLayout drawerPanel;
    private boolean drawerOpen;

    // Every signed-in screen is wrapped with a top bar and a slide-in navigation drawer.
    @Override
    public void setContentView(View view) {
        profileBack = null;
        drawerPanel = null;
        drawerScrim = null;
        drawerOpen = false;
        publicMapVisible = false;
        if (token == null || token.isEmpty()) {
            super.setContentView(view);
            return;
        }

        FrameLayout root = new FrameLayout(this);
        root.setBackgroundColor(BACKGROUND);

        LinearLayout column = new LinearLayout(this);
        column.setOrientation(LinearLayout.VERTICAL);

        LinearLayout bar = new LinearLayout(this);
        bar.setOrientation(LinearLayout.HORIZONTAL);
        bar.setGravity(Gravity.CENTER_VERTICAL);
        bar.setBackgroundColor(BLUE);
        bar.setPadding(dp(8), dp(8), dp(16), dp(8));
        bar.setElevation(dp(4));
        TextView menu = new TextView(this);
        menu.setText("☰");
        menu.setTextColor(0xFFFFFFFF);
        menu.setTextSize(26);
        menu.setGravity(Gravity.CENTER);
        menu.setContentDescription("Open menu");
        menu.setOnClickListener(v -> openDrawer());
        bar.addView(menu, new LinearLayout.LayoutParams(dp(48), dp(48)));
        TextView title = new TextView(this);
        title.setText("SwineLocate");
        title.setTextColor(0xFFFFFFFF);
        title.setTextSize(19);
        title.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        bar.addView(title, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT));
        column.addView(bar, matchWidth());
        column.addView(view, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, 0, 1f));
        root.addView(column, new FrameLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));

        drawerScrim = new View(this);
        drawerScrim.setBackgroundColor(0x88000000);
        drawerScrim.setVisibility(View.GONE);
        drawerScrim.setOnClickListener(v -> closeDrawer());
        root.addView(drawerScrim, new FrameLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));

        drawerPanel = buildDrawer();
        drawerPanel.setVisibility(View.INVISIBLE);
        int width = Math.min(dp(300), (int) (getResources().getDisplayMetrics().widthPixels * 0.82f));
        root.addView(drawerPanel, new FrameLayout.LayoutParams(
                width, ViewGroup.LayoutParams.MATCH_PARENT, Gravity.START));
        root.setOnApplyWindowInsetsListener((v, insets) -> {
            int top = insets.getSystemWindowInsetTop();
            int bottom = insets.getSystemWindowInsetBottom();
            bar.setPadding(dp(8), dp(8) + top, dp(16), dp(8));
            view.setPadding(view.getPaddingLeft(), view.getPaddingTop(), view.getPaddingRight(),
                    Math.max(view.getPaddingBottom(), bottom));
            drawerPanel.setPadding(0, 0, 0, bottom);
            drawerHeader.setPadding(dp(20), dp(18) + top, dp(20), dp(18));
            return insets;
        });
        super.setContentView(root);
        root.requestApplyInsets();
    }

    private LinearLayout drawerHeader;
    private String activeMenu = "dashboard";

    private LinearLayout buildDrawer() {
        LinearLayout panel = new LinearLayout(this);
        panel.setOrientation(LinearLayout.VERTICAL);
        panel.setBackgroundColor(CARD);
        panel.setElevation(dp(16));

        JSONObject user = dashboard == null ? null : dashboard.optJSONObject("user");
        LinearLayout header = new LinearLayout(this);
        header.setOrientation(LinearLayout.VERTICAL);
        header.setBackgroundColor(BLUE);
        header.setPadding(dp(20), dp(36), dp(20), dp(18));
        drawerHeader = header;
        TextView name = new TextView(this);
        name.setText(user == null ? "SwineLocate" : user.optString("name", "SwineLocate"));
        name.setTextColor(0xFFFFFFFF);
        name.setTextSize(19);
        name.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        header.addView(name);
        String role = user == null ? "" : user.optString("role", "");
        String farm = user == null ? "" : user.optString("farm", "");
        String subtitle = role + (farm.isEmpty() || "null".equals(farm) ? "" : " · " + farm);
        if (!subtitle.isEmpty()) {
            TextView sub = new TextView(this);
            sub.setText(subtitle);
            sub.setTextColor(0xCCFFFFFF);
            sub.setTextSize(13);
            header.addView(sub);
        }
        panel.addView(header, matchWidth());

        ScrollView scroll = new ScrollView(this);
        LinearLayout items = new LinearLayout(this);
        items.setOrientation(LinearLayout.VERTICAL);
        items.setPadding(0, dp(8), 0, dp(32));
        scroll.addView(items);
        panel.addView(scroll, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, 0, 1f));

        addDrawerItem(items, "🏠  Dashboard", "dashboard", false, () -> {
            if (dashboard != null) {
                renderDashboard();
            } else {
                loadDashboard();
            }
        });

        Set<String> available = new HashSet<>();
        JSONArray modules = dashboard == null ? null : dashboard.optJSONArray("modules");
        for (int index = 0; modules != null && index < modules.length(); index++) {
            JSONObject module = modules.optJSONObject(index);
            if (module != null) {
                available.add(module.optString("id"));
            }
        }

        // Same sections and order as the web sidebar.
        String[][] sections = {
                {"User & Access", "users:Users", "roles:Roles & Permissions"},
                {"Farm Management", "farms:Farms", "locations:Farm Locations", "farmers:Farmers",
                        "swine:Swine", "register-swine:Register Swine"},
                {"Veterinary Management", "health-records:Health Records", "vaccinations:Vaccinations"},
                {"Farm Operations", "weight-records:Weight Records", "growth:Growth Monitoring",
                        "movements:Swine Movements"},
                {"QR & Traceability", "@scan:Scan QR", "traceability:Traceability"},
                {"Data Synchronization", "@sync:Sync Status"},
                {"Reports & Analytics", "reports:Reports & Analytics"},
        };
        for (String[] section : sections) {
            boolean headerAdded = false;
            for (int index = 1; index < section.length; index++) {
                String id = section[index].substring(0, section[index].indexOf(':'));
                String title = section[index].substring(section[index].indexOf(':') + 1);
                boolean visible;
                if ("@scan".equals(id)) {
                    visible = hasPermission("scan-qr");
                } else if ("@sync".equals(id)) {
                    visible = true;
                } else {
                    visible = available.contains(id);
                }
                if (!visible) {
                    continue;
                }
                if (!headerAdded) {
                    addDrawerSection(items, section[0]);
                    headerAdded = true;
                }
                if ("@scan".equals(id)) {
                    addDrawerItem(items, title, "scan", false, this::scanQr);
                } else if ("@sync".equals(id)) {
                    int waiting = offlineStore.pendingCount();
                    addDrawerItem(items, title + (waiting > 0 ? "  (" + waiting + " waiting)" : ""),
                            "sync", false, () -> syncPending(true));
                } else {
                    addDrawerItem(items, title, id, false, () -> {
                        if ("register-swine".equals(id)) {
                            createResource("swine");
                        } else {
                            showResource(id, 1, "");
                        }
                    });
                }
            }
        }

        View divider = new View(this);
        divider.setBackgroundColor(0xFFE5ECF3);
        LinearLayout.LayoutParams dividerParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, dp(1));
        dividerParams.topMargin = dp(8);
        items.addView(divider, dividerParams);
        addDrawerItem(items, "Farmers map", "map", false, () -> showFarmersMap("farms"));
        addDrawerItem(items, "Sign out", "signout", true, this::signOut);
        return panel;
    }

    private void addDrawerSection(LinearLayout parent, String title) {
        TextView heading = new TextView(this);
        heading.setText(title.toUpperCase(Locale.US));
        heading.setTextColor(0xFF9AA8B6);
        heading.setTextSize(11);
        heading.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        heading.setLetterSpacing(0.08f);
        heading.setPadding(dp(20), dp(16), dp(20), dp(4));
        parent.addView(heading, matchWidth());
    }

    private void addDrawerItem(LinearLayout parent, String text, String key, boolean danger, Runnable action) {
        boolean active = key.equals(activeMenu);
        TextView item = new TextView(this);
        item.setText(text);
        item.setTextSize(16);
        item.setTextColor(danger ? 0xFFB91C1C : active ? BLUE : DARK);
        if (active) {
            item.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        }
        item.setPadding(dp(20), dp(14), dp(20), dp(14));
        android.util.TypedValue outValue = new android.util.TypedValue();
        if (active) {
            item.setBackgroundColor(0xFFE3EEFB);
        } else if (getTheme().resolveAttribute(android.R.attr.selectableItemBackground, outValue, true)) {
            item.setBackgroundResource(outValue.resourceId);
        }
        item.setOnClickListener(v -> {
            closeDrawer();
            action.run();
        });
        parent.addView(item, matchWidth());
    }

    private void openDrawer() {
        if (drawerPanel == null || drawerOpen) {
            return;
        }
        drawerOpen = true;
        drawerScrim.setAlpha(0f);
        drawerScrim.setVisibility(View.VISIBLE);
        drawerScrim.animate().alpha(1f).setDuration(180).start();
        drawerPanel.setTranslationX(-dp(320));
        drawerPanel.setVisibility(View.VISIBLE);
        drawerPanel.animate().translationX(0).setDuration(200).start();
    }

    private void closeDrawer() {
        if (drawerPanel == null || !drawerOpen) {
            return;
        }
        drawerOpen = false;
        View scrim = drawerScrim;
        LinearLayout panel = drawerPanel;
        scrim.animate().alpha(0f).setDuration(160).withEndAction(() -> scrim.setVisibility(View.GONE)).start();
        panel.animate().translationX(-dp(320)).setDuration(180)
                .withEndAction(() -> panel.setVisibility(View.INVISIBLE)).start();
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
        publicMap.setWebViewClient(farmerLinkClient());
        screen.addView(publicMap, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                0,
                1.0f));
        super.setContentView(screen);
        profileBack = null;

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
                    offlineStore.clear();
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
            prefetchOfflineData();
            syncPending(false);
            openPendingFarmerProfile();
        }, exception -> showDashboardFailure(exception.getMessage()));
    }

    private String pendingFarmerToken;
    private String pendingSwineUrl;

    private boolean isSwineScanUrl(Uri uri) {
        if (uri == null || uri.getHost() == null
                || !uri.getHost().equalsIgnoreCase(Uri.parse(BuildConfig.API_BASE_URL).getHost())) {
            return false;
        }
        List<String> segments = uri.getPathSegments();
        int size = segments.size();
        return size >= 3 && "scan".equals(segments.get(size - 2))
                && ("qr".equals(segments.get(size - 3)) || "swine".equals(segments.get(size - 3)));
    }

    private void handleSwineLink(String url) {
        if (token == null || token.isEmpty()) {
            pendingSwineUrl = url;
            showMessage("Sign in to view this swine.");
            showLogin();
            return;
        }
        lookupQr(url);
    }

    // Returns the farmer QR token when the URL is a SwineLocate farmer profile link.
    private String farmerTokenFromUrl(Uri uri) {
        if (uri == null || uri.getHost() == null
                || !uri.getHost().equalsIgnoreCase(Uri.parse(BuildConfig.API_BASE_URL).getHost())) {
            return null;
        }
        List<String> segments = uri.getPathSegments();
        int size = segments.size();
        if (size >= 3 && "public".equals(segments.get(size - 3)) && "farmers".equals(segments.get(size - 2))) {
            return segments.get(size - 1);
        }
        return null;
    }

    private void openPendingFarmerProfile() {
        if (pendingSwineUrl != null) {
            String url = pendingSwineUrl;
            pendingSwineUrl = null;
            lookupQr(url);
            return;
        }
        if (pendingFarmerToken != null) {
            String farmerToken = pendingFarmerToken;
            pendingFarmerToken = null;
            showFarmerProfile(farmerToken);
        }
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        if (isSwineScanUrl(intent.getData())) {
            handleSwineLink(intent.getData().toString());
            return;
        }
        String farmerToken = farmerTokenFromUrl(intent.getData());
        if (farmerToken != null) {
            showFarmerProfile(farmerToken);
        }
    }

    private Runnable profileBack;

    private void showFarmerProfile(String farmerToken) {
        boolean signedIn = token != null && !token.isEmpty();
        Runnable back = () -> {
            if (!signedIn) {
                showPublicNearby();
            } else {
                showFarmersMap("farms");
            }
        };
        showLoading("Loading farmer profile…");
        try {
            get("/public/farmers/" + URLEncoder.encode(farmerToken, StandardCharsets.UTF_8.name()), response -> {
                JSONObject farmer = response.optJSONObject("data");
                if (farmer == null) {
                    showMessage("Farmer profile not found.");
                    back.run();
                    return;
                }
                renderFarmerProfile(farmer, back);
            }, exception -> {
                showMessage(exception.getMessage());
                back.run();
            });
        } catch (java.io.UnsupportedEncodingException exception) {
            showMessage("Unable to read the farmer QR code.");
            back.run();
        }
    }

    private void renderFarmerProfile(JSONObject farmer, Runnable back) {
        activeMenu = "";
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(20), dp(20), dp(20), dp(20));
        Button backButton = button("‹  Back");
        panel.addView(backButton, matchWidth());
        backButton.setOnClickListener(view -> back.run());
        addText(panel, "SwineLocate Farmer Profile", 13, 0xFF526577, true);
        addText(panel, farmer.optString("name", "Farmer"), 26, DARK, true);
        JSONObject farm = farmer.optJSONObject("farm");
        if (farm != null) {
            addText(panel, farm.optString("name"), 16, DARK, true);
            StringBuilder place = new StringBuilder();
            for (String key : new String[]{"address", "municipality", "province", "region"}) {
                String part = farm.isNull(key) ? "" : farm.optString(key);
                if (!part.isEmpty()) {
                    place.append(place.length() == 0 ? "" : ", ").append(part);
                }
            }
            if (place.length() > 0) {
                addText(panel, place.toString(), 14, 0xFF526577, false);
            }
        }
        addPanel(page, panel);

        JSONArray listings = farmer.optJSONArray("listings");
        LinearLayout heading = card();
        heading.setPadding(dp(20), dp(14), dp(20), dp(14));
        int count = listings == null ? 0 : listings.length();
        addText(heading, "Available swine (" + count + ")", 18, DARK, true);
        if (count == 0) {
            addText(heading, "No swine are currently listed for sale.", 14, 0xFF526577, false);
        }
        addPanel(page, heading);
        for (int index = 0; index < count; index++) {
            JSONObject item = listings.optJSONObject(index);
            if (item == null) {
                continue;
            }
            LinearLayout card = card();
            card.setPadding(dp(20), dp(14), dp(20), dp(14));
            String breed = item.isNull("breed") ? "Swine" : item.optString("breed", "Swine");
            addText(card, breed + (item.isNull("sex") ? "" : " · " + label(item.optString("sex"))), 16, DARK, true);
            if (!item.isNull("birth_date")) {
                addText(card, "Born: " + item.optString("birth_date"), 14, 0xFF526577, false);
            }
            if (!item.isNull("latest_weight")) {
                addText(card, "Weight: " + item.optString("latest_weight") + " kg"
                        + (item.isNull("weight_date") ? "" : " (" + item.optString("weight_date") + ")"),
                        14, 0xFF526577, false);
            }
            if (!item.isNull("asking_price")) {
                addText(card, "Asking price: ₱" + item.optString("asking_price"), 15, BLUE, true);
            }
            addPanel(page, card);
        }
        setContentView(page);
        profileBack = back;
    }

    private WebViewClient farmerLinkClient() {
        return new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, android.webkit.WebResourceRequest request) {
                String farmerToken = farmerTokenFromUrl(request.getUrl());
                if (farmerToken != null) {
                    showFarmerProfile(farmerToken);
                    return true;
                }
                return false;
            }
        };
    }

    private void renderDashboard() {
        activeMenu = "dashboard";
        JSONObject user = dashboard.optJSONObject("user");
        permissions = userPermissions(user);
        JSONObject stats = dashboard.optJSONObject("stats");
        ScrollView page = page();

        LinearLayout greeting = card();
        greeting.setPadding(dp(20), dp(20), dp(20), dp(20));
        String name = user == null ? "Welcome" : user.optString("name", "Welcome");
        addText(greeting, "Hello, " + name, 25, DARK, true);
        String role = user == null ? "" : user.optString("role", "");
        String farm = user == null ? "" : user.optString("farm", "");
        if ("null".equals(farm)) {
            farm = "";
        }
        String subtitle = role + (farm.isEmpty() ? "" : " · " + farm);
        if (!subtitle.isEmpty()) {
            addText(greeting, subtitle, 14, 0xFF526577, false);
        }
        addText(greeting, "Open the menu ☰ to browse records.", 13, 0xFF6B7C8D, false);
        addPanel(page, greeting);

        int waiting = offlineStore.pendingCount();
        if (waiting > 0) {
            LinearLayout notice = new LinearLayout(this);
            notice.setPadding(dp(16), dp(12), dp(16), dp(12));
            notice.setBackground(rounded(0xFFFEF3C7, 12, 0xFFFCD34D));
            TextView text = new TextView(this);
            text.setText(waiting + " change" + (waiting == 1 ? "" : "s")
                    + " waiting to sync. Open the menu and choose Sync now.");
            text.setTextColor(0xFF92400E);
            text.setTextSize(14);
            notice.addView(text);
            LinearLayout.LayoutParams noticeParams = matchWidth();
            noticeParams.topMargin = dp(12);
            ((ViewGroup) page.getChildAt(0)).addView(notice, noticeParams);
        }

        if (stats != null && stats.length() > 0) {
            TextView heading = new TextView(this);
            heading.setText("Overview");
            heading.setTextColor(DARK);
            heading.setTextSize(18);
            heading.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
            heading.setPadding(dp(4), dp(20), 0, dp(8));
            ((ViewGroup) page.getChildAt(0)).addView(heading, matchWidth());

            LinearLayout rowLayout = null;
            int column = 0;
            Iterator<String> keys = stats.keys();
            while (keys.hasNext()) {
                String key = keys.next();
                if (column % 2 == 0) {
                    rowLayout = new LinearLayout(this);
                    rowLayout.setOrientation(LinearLayout.HORIZONTAL);
                    LinearLayout.LayoutParams rowParams = matchWidth();
                    rowParams.bottomMargin = dp(10);
                    ((ViewGroup) page.getChildAt(0)).addView(rowLayout, rowParams);
                }
                rowLayout.addView(statCard(label(key), String.valueOf(stats.opt(key)), column % 2 == 1));
                column++;
            }
            if (column % 2 == 1) {
                View spacer = new View(this);
                rowLayout.addView(spacer, new LinearLayout.LayoutParams(0, 1, 1f));
            }
        }
        setContentView(page);
    }

    private View statCard(String caption, String value, boolean second) {
        LinearLayout box = new LinearLayout(this);
        box.setOrientation(LinearLayout.VERTICAL);
        box.setPadding(dp(16), dp(16), dp(16), dp(16));
        box.setBackground(rounded(CARD, 14, 0xFFE5ECF3));
        TextView number = new TextView(this);
        number.setText(value);
        number.setTextColor(BLUE);
        number.setTextSize(28);
        number.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        box.addView(number);
        TextView name = new TextView(this);
        name.setText(caption);
        name.setTextColor(0xFF526577);
        name.setTextSize(13);
        box.addView(name);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f);
        if (second) {
            params.leftMargin = dp(10);
        }
        box.setLayoutParams(params);
        return box;
    }

    private void showResource(String resource, int pageNumber, String search) {
        activeMenu = resource;
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
                if (data == null) {
                    results.setText("No report data.");
                } else {
                    panel.removeView(results);
                    addDetailRows(panel, data, 0);
                }
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
        row.setPadding(dp(16), dp(14), dp(16), dp(14));
        row.setBackground(rounded(0xFFF2F6FA, 12, 0xFFDCE6EF));

        String title = recordTitle(resource, record);
        String subtitle = recordSubtitle(resource, record);
        String badge = firstValue(record, "status", "health_status");

        LinearLayout top = new LinearLayout(this);
        top.setOrientation(LinearLayout.HORIZONTAL);
        top.setGravity(Gravity.CENTER_VERTICAL);
        TextView heading = new TextView(this);
        heading.setText(title);
        heading.setTextColor(DARK);
        heading.setTextSize(16);
        heading.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        top.addView(heading, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f));
        if (!badge.isEmpty()) {
            top.addView(badge(badge));
        }
        row.addView(top, matchWidth());

        if (!subtitle.isEmpty()) {
            TextView detail = addText(row, subtitle, 13, 0xFF526577, false);
            detail.setPadding(0, dp(4), 0, 0);
        }
        TextView more = addText(row, "View details  ›", 12, BLUE, true);
        more.setPadding(0, dp(8), 0, 0);

        LinearLayout.LayoutParams params = matchWidth();
        params.bottomMargin = dp(10);
        panel.addView(row, params);
        row.setOnClickListener(view ->
                showRecordDetail(resource, record, () -> showResource(resource, 1, "")));
    }

    private String recordTitle(String resource, JSONObject record) {
        if ("weight-records".equals(resource)) {
            String weight = firstValue(record, "weight");
            return weight.isEmpty() ? "Weight record" : weight + " kg";
        }
        if ("movements".equals(resource)) {
            String tag = nestedValue(record, "swine", "tag_number");
            return tag.isEmpty() ? "Movement" : "Movement · " + tag;
        }
        if ("health-records".equals(resource) || "vaccinations".equals(resource)) {
            String type = firstValue(record, "vaccine_name", "record_type");
            String tag = nestedValue(record, "swine", "tag_number");
            return (type.isEmpty() ? "Health record" : type) + (tag.isEmpty() ? "" : " · " + tag);
        }
        String title = firstValue(record, "tag_number", "name", "farm_code", "location_code", "record_type", "reason");
        return title.isEmpty() ? "Record #" + record.optString("id") : title;
    }

    private String recordSubtitle(String resource, JSONObject record) {
        List<String> parts = new ArrayList<>();
        switch (resource) {
            case "swine":
                parts.add(label(firstValue(record, "sex")));
                parts.add(firstValue(record, "breed"));
                parts.add(nestedValue(record, "farm", "name"));
                if (record.optBoolean("is_for_sale", false)) {
                    String price = firstValue(record, "asking_price");
                    parts.add(price.isEmpty() ? "For sale" : "For sale ₱" + price);
                }
                break;
            case "farms":
                parts.add(firstValue(record, "farm_code"));
                parts.add(firstValue(record, "municipality"));
                parts.add(firstValue(record, "province"));
                break;
            case "farmers":
                parts.add(firstValue(record, "contact_number"));
                parts.add(nestedValue(record, "farm", "name"));
                break;
            case "locations":
                parts.add(firstValue(record, "location_code"));
                parts.add(firstValue(record, "type"));
                parts.add(nestedValue(record, "farm", "name"));
                break;
            case "users":
                parts.add(firstValue(record, "email"));
                parts.add(nestedValue(record, "role", "name"));
                break;
            case "roles":
                JSONArray granted = record.optJSONArray("permissions");
                parts.add((granted == null ? 0 : granted.length()) + " permissions");
                break;
            case "weight-records":
                parts.add(nestedValue(record, "swine", "tag_number"));
                parts.add(shortDate(firstValue(record, "record_date")));
                break;
            case "health-records":
            case "vaccinations":
                parts.add(shortDate(firstValue(record, "record_date")));
                parts.add(label(firstValue(record, "record_type")));
                break;
            case "movements":
                String from = nestedValue(record, "from_location", "name");
                String to = nestedValue(record, "to_location", "name");
                if (!to.isEmpty()) {
                    parts.add((from.isEmpty() ? "" : from + " → ") + to);
                }
                parts.add(shortDate(firstValue(record, "movement_date")));
                break;
            default:
                parts.add(firstValue(record, "email", "breed"));
                break;
        }
        List<String> filled = new ArrayList<>();
        for (String part : parts) {
            if (part != null && !part.isEmpty()) {
                filled.add(part);
            }
        }
        return android.text.TextUtils.join("  ·  ", filled);
    }

    private String nestedValue(JSONObject record, String relation, String key) {
        JSONObject nested = record.optJSONObject(relation);
        return nested == null ? "" : firstValue(nested, key);
    }

    private String shortDate(String value) {
        return value != null && value.length() >= 10 && value.charAt(4) == '-' ? value.substring(0, 10) : value;
    }

    private TextView badge(String value) {
        String key = value.toLowerCase(Locale.US);
        int color = 0xFFB45309;
        int background = 0xFFFEF3C7;
        if (key.equals("active") || key.equals("healthy")) {
            color = 0xFF166534;
            background = 0xFFDCFCE7;
        } else if (key.equals("inactive") || key.equals("sick") || key.equals("deceased")) {
            color = 0xFF991B1B;
            background = 0xFFFEE2E2;
        }
        TextView chip = new TextView(this);
        chip.setText(label(value));
        chip.setTextColor(color);
        chip.setTextSize(12);
        chip.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        chip.setPadding(dp(10), dp(4), dp(10), dp(4));
        chip.setBackground(rounded(background, 12, background));
        return chip;
    }

    private android.graphics.drawable.GradientDrawable rounded(int fill, int radiusDp, int stroke) {
        android.graphics.drawable.GradientDrawable drawable = new android.graphics.drawable.GradientDrawable();
        drawable.setColor(fill);
        drawable.setCornerRadius(dp(radiusDp));
        drawable.setStroke(dp(1), stroke);
        return drawable;
    }

    private void showRecordDetail(String resource, JSONObject record, Runnable back) {
        ScrollView page = page();
        LinearLayout panel = card();
        panel.setPadding(dp(20), dp(20), dp(20), dp(20));
        Button backButton = button("‹  Back");
        panel.addView(backButton, matchWidth());
        backButton.setOnClickListener(view -> back.run());

        String title = recordTitle(resource, record);
        LinearLayout header = new LinearLayout(this);
        header.setOrientation(LinearLayout.HORIZONTAL);
        header.setGravity(Gravity.CENTER_VERTICAL);
        TextView heading = new TextView(this);
        heading.setText(title);
        heading.setTextColor(DARK);
        heading.setTextSize(24);
        heading.setTypeface(Typeface.DEFAULT, Typeface.BOLD);
        header.addView(heading, new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f));
        String badge = firstValue(record, "status", "health_status");
        if (!badge.isEmpty()) {
            header.addView(badge(badge));
        }
        panel.addView(header, matchWidth());
        addText(panel, label(resource), 13, 0xFF526577, false);

        addDetailRows(panel, record, 0);

        if (canEdit(resource)) {
            Button edit = button("Edit");
            panel.addView(edit, matchWidth());
            edit.setOnClickListener(view -> openForm(resource, record));
        }
        if (canDelete(resource)) {
            Button delete = button("farms".equals(resource) || "locations".equals(resource) || "swine".equals(resource)
                    ? "Deactivate" : "Delete");
            delete.setTextColor(0xFFB91C1C);
            panel.addView(delete, matchWidth());
            delete.setOnClickListener(view -> confirmDelete(resource, record, title));
        }
        addPanel(page, panel);
        setContentView(page);
    }

    private boolean isHiddenKey(String key) {
        return key.equals("id") || key.equals("password") || key.equals("pivot") || key.equals("local_id")
                || key.equals("created_at") || key.equals("updated_at") || key.equals("deleted_at")
                || key.equals("email_verified_at") || key.endsWith("_id") || key.endsWith("_token");
    }

    private void addDetailRows(LinearLayout parent, JSONObject object, int depth) {
        List<String> nested = new ArrayList<>();
        Iterator<String> keys = object.keys();
        while (keys.hasNext()) {
            String key = keys.next();
            Object value = object.opt(key);
            if (isHiddenKey(key) || ("name".equals(key) && object.has("tag_number"))) {
                continue;
            }
            if (value instanceof JSONObject || value instanceof JSONArray) {
                nested.add(key);
            } else {
                addDetailRow(parent, label(key), formatValue(key, value));
            }
        }
        for (String key : nested) {
            Object value = object.opt(key);
            if (value instanceof JSONObject) {
                if (depth >= 2) {
                    continue;
                }
                TextView section = addText(parent, label(key), 15, BLUE, true);
                section.setPadding(0, dp(16), 0, dp(2));
                LinearLayout box = new LinearLayout(this);
                box.setOrientation(LinearLayout.VERTICAL);
                box.setPadding(dp(12), dp(4), dp(12), dp(8));
                box.setBackground(rounded(0xFFF2F6FA, 10, 0xFFDCE6EF));
                addDetailRows(box, (JSONObject) value, depth + 1);
                parent.addView(box, matchWidth());
            } else {
                JSONArray items = (JSONArray) value;
                TextView section = addText(parent, label(key) + " (" + items.length() + ")", 15, BLUE, true);
                section.setPadding(0, dp(16), 0, dp(2));
                for (int index = 0; index < items.length() && index < 25; index++) {
                    Object item = items.opt(index);
                    String line = item instanceof JSONObject ? summarize((JSONObject) item) : String.valueOf(item);
                    addText(parent, "•  " + line, 14, 0xFF283C50, false);
                }
            }
        }
    }

    private String summarize(JSONObject object) {
        String name = object.has("tag_number")
                ? firstValue(object, "tag_number")
                : firstValue(object, "name", "tag_number", "slug", "label", "record_type");
        if (!name.isEmpty()) {
            return name;
        }
        List<String> parts = new ArrayList<>();
        Iterator<String> keys = object.keys();
        while (keys.hasNext() && parts.size() < 4) {
            String key = keys.next();
            Object value = object.opt(key);
            if (!isHiddenKey(key) && !(value instanceof JSONObject) && !(value instanceof JSONArray)) {
                parts.add(label(key) + ": " + formatValue(key, value));
            }
        }
        return android.text.TextUtils.join("  ·  ", parts);
    }

    private void addDetailRow(LinearLayout parent, String name, String value) {
        LinearLayout row = new LinearLayout(this);
        row.setOrientation(LinearLayout.VERTICAL);
        row.setPadding(0, dp(8), 0, dp(8));
        TextView caption = new TextView(this);
        caption.setText(name);
        caption.setTextColor(0xFF6B7C8D);
        caption.setTextSize(12);
        row.addView(caption);
        TextView content = new TextView(this);
        content.setText(value);
        content.setTextColor(DARK);
        content.setTextSize(16);
        content.setTextIsSelectable(true);
        row.addView(content);
        parent.addView(row, matchWidth());
        View divider = new View(this);
        divider.setBackgroundColor(0xFFE5ECF3);
        parent.addView(divider, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, dp(1)));
    }

    private String formatValue(String key, Object value) {
        if (value == null || value == JSONObject.NULL) {
            return "—";
        }
        String text = String.valueOf(value);
        if (text.isEmpty()) {
            return "—";
        }
        if (value instanceof Boolean) {
            return (Boolean) value ? "Yes" : "No";
        }
        if (key.startsWith("is_") && (text.equals("1") || text.equals("0"))) {
            return text.equals("1") ? "Yes" : "No";
        }
        if (key.contains("price")) {
            return "₱" + text;
        }
        if (key.equals("weight")) {
            return text + " kg";
        }
        if (text.length() >= 19 && text.charAt(4) == '-' && text.charAt(10) == 'T') {
            return text.substring(0, 10) + " " + text.substring(11, 16);
        }
        if (key.equals("status") || key.equals("sex") || key.equals("health_status") || key.equals("record_type")) {
            return label(text);
        }
        return text;
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
        if (existing == null && "swine".equals(resource)) {
            activeMenu = "register-swine";
        } else {
            activeMenu = resource;
        }
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

        // Farmer and location choices follow the selected farm.
        Map<String, List<java.util.function.IntConsumer>> refreshers = new HashMap<>();
        for (FormField definition : fields) {
            String parentKey = parentFarmKey(definition.key);
            Spinner child = selectors.get(definition.key);
            Spinner parent = parentKey == null ? null : selectors.get(parentKey);
            if (child == null || parent == null || definition.optionsKey == null) {
                continue;
            }
            String[] pending = {existing == null || existing.isNull(definition.key)
                    ? "" : existing.optString(definition.key)};
            java.util.function.IntConsumer refresh = parentPosition -> {
                String farmId = fieldValueAt(fields, parentKey, parentPosition);
                List<String> values = new ArrayList<>();
                List<String> labels = new ArrayList<>();
                values.add("");
                JSONArray records = options.optJSONArray(definition.optionsKey);
                for (int index = 0; !farmId.isEmpty() && records != null && index < records.length(); index++) {
                    JSONObject record = records.optJSONObject(index);
                    if (record != null && farmId.equals(record.optString("farm_id"))) {
                        values.add(record.optString("id"));
                        labels.add(optionLabel(definition.optionsKey, record));
                    }
                }
                labels.add(0, farmId.isEmpty() ? "Select a farm first"
                        : values.size() == 1 ? "No " + label(definition.key).toLowerCase(Locale.US) + " for this farm"
                        : definition.required ? "Choose " + label(definition.key) : "Not selected");
                ArrayAdapter<String> adapter = new ArrayAdapter<>(
                        this, android.R.layout.simple_spinner_item, labels);
                adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
                child.setAdapter(adapter);
                definition.values = values;
                int position = values.indexOf(pending[0]);
                if (position < 0 && values.size() == 2 && definition.required) {
                    position = 1;
                }
                child.setSelection(Math.max(position, 0));
                pending[0] = "";
            };
            refreshers.computeIfAbsent(parentKey, k -> new ArrayList<>()).add(refresh);
            refresh.accept(parent.getSelectedItemPosition());
        }

        for (Map.Entry<String, List<java.util.function.IntConsumer>> entry : refreshers.entrySet()) {
            Spinner parent = selectors.get(entry.getKey());
            List<java.util.function.IntConsumer> listeners = entry.getValue();
            parent.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> adapterView, View view, int position, long id) {
                    for (java.util.function.IntConsumer listener : listeners) {
                        listener.accept(position);
                    }
                }

                @Override
                public void onNothingSelected(AdapterView<?> adapterView) {
                }
            });
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

    private String parentFarmKey(String key) {
        switch (key) {
            case "farmer_id":
            case "current_location_id":
                return "farm_id";
            case "to_farmer_id":
            case "to_location_id":
                return "to_farm_id";
            default:
                return null;
        }
    }

    private String fieldValueAt(List<FormField> fields, String key, int position) {
        for (FormField field : fields) {
            if (key.equals(field.key) && field.values != null) {
                return position >= 0 && position < field.values.size() ? field.values.get(position) : "";
            }
        }
        return "";
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
        String name = option.has("tag_number") ? firstValue(option, "tag_number") : firstValue(option, "name", "tag_number");
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
        activeMenu = "scan";
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
        String farmerToken = farmerTokenFromUrl(Uri.parse(value));
        if (farmerToken != null) {
            showFarmerProfile(farmerToken);
            return;
        }
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
                showRecordDetail("swine", swine, this::renderDashboard);
            }, exception -> showMessage(exception.getMessage()));
        } catch (java.io.UnsupportedEncodingException exception) {
            showMessage("Unable to read the QR code.");
        }
    }

    private void signOut() {
        int waiting = offlineStore.pendingCount();
        if (waiting > 0) {
            new AlertDialog.Builder(this)
                    .setTitle("Unsynced changes")
                    .setMessage(waiting + " offline change(s) have not synced yet and will be lost if you sign out.")
                    .setNegativeButton("Stay signed in", null)
                    .setPositiveButton("Sign out anyway", (d, which) -> performSignOut())
                    .show();
            return;
        }
        performSignOut();
    }

    private void performSignOut() {
        post("/logout", new JSONObject(), token, response -> signOutLocally(), exception -> {
            signOutLocally();
            showMessage(exception.getMessage());
        });
    }

    private void signOutLocally() {
        tokenStore.clear();
        offlineStore.clear();
        mainHandler.removeCallbacks(syncRunnable);
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
        activeMenu = "map";
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
        publicMap.setWebViewClient(farmerLinkClient());
        screen.addView(publicMap, new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                0,
                1.0f));
        super.setContentView(screen);
        profileBack = null;

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
                offlineStore.cacheResponse(path, result);
                mainHandler.post(() -> callback.complete(result));
            } catch (ApiException exception) {
                mainHandler.post(() -> errorCallback.failed(exception));
            } catch (Exception exception) {
                JSONObject saved = offlineStore.cachedResponse(path);
                if (saved == null) {
                    mainHandler.post(() -> errorCallback.failed(new IOException(
                            "You are offline and this has not been saved on your phone yet.", exception)));
                } else {
                    mainHandler.post(() -> {
                        showMessage("Offline – showing the last saved data.");
                        callback.complete(saved);
                    });
                }
            }
        });
    }

    private void send(String method, String path, JSONObject body, Callback callback, ErrorCallback errorCallback) {
        String key = UUID.randomUUID().toString();
        executor.execute(() -> {
            try {
                JSONObject result = perform(method, path, body, key);
                mainHandler.post(() -> callback.complete(result));
            } catch (ApiException exception) {
                mainHandler.post(() -> errorCallback.failed(exception));
            } catch (Exception exception) {
                try {
                    offlineStore.enqueue(new OfflineStore.PendingChange(key, method, path, body));
                    mainHandler.post(() -> {
                        showMessage("No connection. Saved on this phone and will sync automatically.");
                        scheduleSync();
                        callback.complete(new JSONObject());
                    });
                } catch (IOException storageFailure) {
                    mainHandler.post(() -> errorCallback.failed(storageFailure));
                }
            }
        });
    }

    private JSONObject perform(String method, String path, JSONObject body, String key) throws IOException {
        if ("PUT".equals(method)) {
            return api.put(path, body, token, key);
        }
        if ("DELETE".equals(method)) {
            return api.delete(path, token, key);
        }
        return api.post(path, body, token, key);
    }

    private void scheduleSync() {
        mainHandler.removeCallbacks(syncRunnable);
        if (token != null && offlineStore.pendingCount() > 0) {
            mainHandler.postDelayed(syncRunnable, 30000);
        }
    }

    private void syncPending(boolean manual) {
        if (token == null || syncing || offlineStore.pendingCount() == 0) {
            if (manual) {
                showMessage("Nothing to sync.");
            }
            return;
        }
        syncing = true;
        executor.execute(() -> {
            int synced = 0;
            List<String> rejected = new ArrayList<>();
            boolean reachable = true;
            for (OfflineStore.PendingChange change : offlineStore.pending()) {
                try {
                    perform(change.method, change.path, change.body, change.key);
                    offlineStore.remove(change.key);
                    synced++;
                } catch (ApiException exception) {
                    if (exception.isRetryable()) {
                        reachable = false;
                        break;
                    }
                    // The server refused it (validation, permission or removed record); retrying cannot help.
                    offlineStore.remove(change.key);
                    rejected.add(change.method + " " + change.path + ": " + exception.getMessage());
                } catch (IOException exception) {
                    reachable = false;
                    break;
                }
            }
            int syncedCount = synced;
            boolean stillReachable = reachable;
            mainHandler.post(() -> {
                syncing = false;
                if (!rejected.isEmpty()) {
                    new AlertDialog.Builder(this)
                            .setTitle("Some offline changes were rejected")
                            .setMessage(android.text.TextUtils.join("\n\n", rejected))
                            .setPositiveButton("OK", null)
                            .show();
                } else if (syncedCount > 0) {
                    showMessage("Synced " + syncedCount + " offline change" + (syncedCount == 1 ? "" : "s") + ".");
                } else if (manual && !stillReachable) {
                    showMessage("Still offline. Changes will sync when the connection returns.");
                }
                scheduleSync();
            });
        });
    }

    private void prefetchOfflineData() {
        JSONArray modules = dashboard == null ? null : dashboard.optJSONArray("modules");
        executor.execute(() -> {
            for (int index = 0; modules != null && index < modules.length(); index++) {
                JSONObject module = modules.optJSONObject(index);
                String id = module == null ? "" : module.optString("id");
                if (id.isEmpty() || "register-swine".equals(id)) {
                    continue;
                }
                try {
                    String path = "/data/" + id + "?page=1";
                    offlineStore.cacheResponse(path, api.get(path, token));
                } catch (Exception ignored) {
                    return;
                }
            }
            for (String resource : new String[] {"swine", "health-records", "weight-records", "movements",
                    "farmers", "locations", "users", "roles"}) {
                try {
                    String path = "/options/" + resource;
                    offlineStore.cacheResponse(path, api.get(path, token));
                } catch (ApiException ignored) {
                    // No permission for this form; skip it.
                } catch (Exception exception) {
                    return;
                }
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
        panel.setBackground(rounded(CARD, 16, 0xFFE5ECF3));
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
