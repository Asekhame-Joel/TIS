# Cache and deployment configuration

The site uses two complementary cache policies:

- Dynamic HTML pages must revalidate on every visit and are not stored at the CDN edge.
- Files under `/assets/` use content-hash query strings and may be cached for one year as immutable resources.

`tis_asset()` calculates the version from the deployed file contents. When a CSS, JavaScript, or image file changes, its public URL changes automatically, for example:

```text
/assets/css/style.css?v=8e3d974c15b1
```

No version number needs to be edited manually during deployment.

## Namecheap cPanel deployment

1. Deploy the entire repository update, including the hidden `public/.htaccess` file.
2. Ensure the domain's document root points to the `public/` directory, as described in the normal deployment setup.
3. Do not configure cPanel's Optimize Website or another plugin to replace the application's `Cache-Control` headers.
4. After deploying this cache-policy change for the first time, clear any server-side cache once. Future application deployments do not require visitors to clear browser caches.

The `.htaccess` rules are guarded with `IfModule`, so they remain compatible with standard Namecheap Apache/LiteSpeed hosting when optional header or expiry modules are unavailable.

## Cloudflare configuration

In **Caching → Configuration**, set **Browser Cache TTL** to **Respect Existing Headers**.

In **Caching → Cache Rules**:

1. Remove or disable any site-wide **Cache Everything** rule that overrides origin cache headers.
2. Remove any rule that uses an **Edge Cache TTL** for every URL regardless of the origin response.
3. Do not use **Ignore Query String** for `/assets/*`; the `v` parameter is the asset version and must remain part of the cache key.
4. If an explicit rule is desired, use `/assets/*` as the only cache-eligible path and set both Edge TTL and Browser TTL to **Respect origin**.
5. Bypass cache for `/api/*`. Payment responses already send `no-store` and must never be cached.

After these settings are corrected, run **Caching → Configuration → Purge Everything** once to remove HTML or assets cached under the old policy. Normal future deployments will not need a full purge because HTML is not retained at the edge and changed assets receive new URLs.

## Verification

For a normal page response, expect:

```text
Cache-Control: no-cache, max-age=0, must-revalidate
CDN-Cache-Control: no-store
Cloudflare-CDN-Cache-Control: no-store
```

For versioned files under `/assets/`, expect:

```text
Cache-Control: public, max-age=31536000, immutable
CDN-Cache-Control: public, max-age=31536000, immutable
Cloudflare-CDN-Cache-Control: public, max-age=31536000, immutable
```

When Cloudflare is active, `CF-Cache-Status` should normally be `DYNAMIC` or `BYPASS` for HTML and may become `HIT` for versioned assets after the first request.
