// ./src/assets/js/user-profile.js
const BASE_URL = "https://e3oneacademy.vercel.app";

function updateUserUI(user) {
    const username = document.querySelector("#username");
    const profileImg = document.querySelector("#profileImg");

    if (username) {
       username.textContent = user.display_name || "";
    }

    if (profileImg) {
        profileImg.src = user.profile_image
            ? `${BASE_URL}/uploads/photos/${user.profile_image}`
            : `${BASE_URL}/src/assets/default-user.png`;
    }
    // console.log("DISPLAY NAME:", user.display_name);

}

function loadCachedUser() {
    const raw = localStorage.getItem("user");
    if (!raw) return;

    try {
        updateUserUI(JSON.parse(raw));
    } catch (error) {
        console.error("Invalid user data in localStorage", error);
        localStorage.removeItem("user");
    }
}

async function refreshUser() {
    try {
        const response = await fetch(`${BASE_URL}/api/v1/users.php`, {
            credentials: "include"
        });

        if (!response.ok) {
            console.error("HTTP Error:", response.status);
            return;
        }

        let data;
        try {
            data = await response.json();
        } catch (e) {
            console.error("Invalid JSON response", e);
            return;
        }

        console.log("Current User:", data);

        if (data.success && data.data) {
            localStorage.setItem("user", JSON.stringify(data.data));
            updateUserUI(data.data);
        }
    } catch (error) {
        console.error("Network error:", error);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    loadCachedUser();
    refreshUser();
});
