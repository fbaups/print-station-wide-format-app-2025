# Backblaze B2 Integration Credentials


This guide walks you through creating a Backblaze B2 bucket via the Backblaze web interface.


## 📝 Create a Bucket
### 1. **Prerequisites**


- A [Backblaze B2 account](https://www.backblaze.com/b2/cloud-storage.html)
- Logged into the [Backblaze B2 Web Console](https://secure.backblaze.com/b2_buckets.htm)

---


### 2. **Log In**

- Go to: [https://secure.backblaze.com](https://secure.backblaze.com)
- Enter your credentials and log in.

---

### 3. **Navigate to Buckets**

- Click on **“Buckets”** in the left-hand sidebar (or go directly
  to [https://secure.backblaze.com/b2_buckets.htm](https://secure.backblaze.com/b2_buckets.htm))

---

### 4. **Create a New Bucket**

- Click the blue **“Create a Bucket”** button.

---

### 5. **Configure Bucket Settings**

| Setting                | Description                                                                |
|------------------------|----------------------------------------------------------------------------|
| **Bucket Name**        | Must be globally unique (e.g. `my-app-storage-123`)                        |
| **Bucket Type**        | Choose between:                                                            |
|                        | - **Private** (default): Only accessible with authorization.               |
|                        | - **Public**: Files accessible via public URLs.                            |
| **Default Encryption** | Choose to enable/disable server-side encryption.                           |
| **Object Lock**        | Optional: enable to prevent deletion for a period of time (WORM)           |
| **S3 API Support**     | Enable if you'll access this bucket via S3-compatible tools like Flysystem |

- Once configured, click **“Create Bucket”**

---

### 6. **Copy Your Bucket Info**

After creation, note the following details:

- **Bucket Name**
- **Bucket ID**
- **Endpoint URL (for S3 API)** — shown if S3 API is enabled

---

## 🛠️ Additional Bucket Configuration
1. Go to **Buckets** in the left sidebar
2. Scroll to the bucket you need to configure
3. click **Lifecycle Settings**.
    - Select **Keep only the last version of the file** or another preference
    - Save the setting
4. click **CORS Rules**.
    - Select **Share everything in this bucket with all HTTPS origins** or another preference
    - Select **Both** for **Apply CORS rules to the following APIs**
    - Save the setting


---

## 🔐 Get Application Key for Programmatic Access

1. Go to **App Keys** in the left sidebar.
2. Click **“Add a New Application Key”**
3. Set:
    - **Name**
    - **Bucket restrictions** (or leave it "All buckets")
    - **Capabilities** (e.g. Read, Write, Delete)
4. Click **Create New Key**
5. Save:
    - **Key ID**
    - **Application Key** (shown only once)

---

## ✅ You're Done!

You now have:

- A B2 bucket ready for uploads
