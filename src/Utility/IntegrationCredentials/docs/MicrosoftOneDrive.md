# Microsoft OneDrive Integration Credentials

This guide walks you through creating an Azure portal application key for integrating with
Microsoft OneDrive.

---

## 📝 Prerequisites

- A Microsoft Azure account: [https://portal.azure.com](https://portal.azure.com)
- Admin access or permissions to register applications

---

## 📝 Step-by-Step Instructions

### 1. **Log in to the Azure Portal**

- Visit: [https://portal.azure.com](https://portal.azure.com)
- Sign in with your Microsoft account

---

### 2. **Register a New Application**

1. In the top search bar, type **"Microsoft Entra ID"**
2. In the left hand panel expand **"Manage"**
3. Click **"App Registrations"**
4. In the main window, click **"+ New Registration"**

Fill out the form:

| Field                       | Description                                                                                                                                      |
|-----------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| **Name**                    | e.g., `My OneDrive App`                                                                                                                          |
| **Supported account types** | Choose Between:<br> - Personal Microsoft accounts only<br> - Accounts in any organizational directory (Any Microsoft Entra ID tenant - Multitenant) and personal Microsoft accounts (e.g. Skype, Xbox) |
| **Redirect URI (optional)** | If using OAuth, set to something like `https://localhost` or your app’s redirect URI                                                             |

4. Click **"Register"**

---

### 3. **Create a Client Secret**

1. In your app registration, go to **"Certificates & secrets"**
2. Under **"Client secrets"**, click **"New client secret"**
3. Enter a description and choose an expiration (e.g., 6 months, 12 months)
4. Click **"Add"**
5. Copy the **Value** immediately — it will not be shown again
6. Copy the **Expiry**

---

### 4. **Collect Required Info**

You will need the following for integration:

- **Application (client) ID**
- **Directory (tenant) ID**
- **Client Secret** (you just created)
- **Redirect URI** (used during OAuth flow)
    - https://your-app-domain.com
    - https://your-app-domain.com/administrators/integration-credentials/authenticate/code
    - https://your-app-domain.com/administrators/integration-credentials/authenticate/microsoft-open-auth-2

---

### 5. **API Permissions**

1. Go to **"API permissions"**
2. Click **"Add a permission"**
3. Choose **Microsoft Graph**
4. Select either:
    - **Delegated permissions** (if acting on behalf of a signed-in user)
    - **Application permissions** (if accessing OneDrive directly)
5. Add permissions like:

| Permission            | Explanation                                                                                       |
|-----------------------|---------------------------------------------------------------------------------------------------|
| `email`               | Grants read access to the user’s primary email address.                                           |
| `Files.Read.All`      | Allows the app to read all files the signed-in user can access across OneDrive or SharePoint.     |
| `Files.ReadWrite.All` | (Only if Required) Allows the app to read, create, update, and delete the signed-in user's files. |
| `offline_access`      | Required to get refresh tokens, so the app can stay signed in after the user closes the browser.  |
| `openid`              | Enables the app to use OpenID Connect for sign-in, required for identity tokens.                  |
| `profile`             | Allows access to basic user profile info such as name and picture.                                |
| `User.Read`           | Allows the app to sign in the user and read their basic profile.                                  |

6. Click **"Grant admin consent"** if required

---

### 6. **Ready for Integration**

Use the collected credentials with your OAuth library or SDK (e.g., Microsoft Graph SDK or `league/oauth2-client` with
`stevenmaguire/oauth2-microsoft`).

---

## ✅ You're Done!

Your application is now registered and ready to authenticate users and interact with OneDrive via Microsoft Graph.
