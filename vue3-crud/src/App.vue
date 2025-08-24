<template>
  <div>
    <!-- Sidebar -->
    <div
      v-if="showNavbar"
      :class="['sidebar', { 'sidebar-open': sidebarOpen }]"
    >
      <router-link
        :to="{ name: 'dashboard.index' }"
        class="sidebar-header text-decoration-none text-white"
      >
        <div class="sidebar-header">
          <div class="logo-container mr-5">
            <div class="orbit orbit-1"></div>
            <div class="orbit orbit-2"></div>
            <div class="digital-core">
              <div class="pixel-grid">
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
                <div class="pixel"></div>
              </div>
            </div>
          </div>
          <span class="fw-bold fs-6">DIGITAL SPACE</span>
        </div>
      </router-link>
      <ul class="sidebar-nav">
        <li>
          <router-link class="size" :to="{ name: 'dashboard.index' }"><i class="bi bi-house-heart-fill"></i> Dashboard</router-link>
        </li>
        <li>
          <router-link class="size" :to="{ name: 'loan.index' }"><i class="bi bi-cart-fill"></i> Peminjaman Buku</router-link>
        </li>
        <li>
          <router-link class="size" :to="{ name: 'member.index' }"><i class="bi bi-person-vcard"></i> Keanggotaan</router-link>
        </li>
        <li>
          <router-link class="size" :to="{ name: 'book.index' }"><i class="bi bi-book-fill"></i> Buku</router-link>
        </li>
        <li>
          <router-link class="size" :to="{ name: 'category.index' }"><i class="bi bi-filter"></i> Kategori</router-link>
        </li>
        <li>
          <router-link class="size" :to="{ name: 'user.index' }"><i class="bi bi-person-gear"></i> User</router-link>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div :class="['main-content', { 'sidebar-opened': sidebarOpen }]">
      <!-- Navbar -->
      <nav v-if="showNavbar" class="navbar bg-light px-3 shadow-sm">
        <div
          class="container-fluid d-flex justify-content-between align-items-center"
        >
          <!-- Kiri: Hamburger + Dashboard -->
          <div class="d-flex align-items-center gap-3">
            <button
              @click="toggleSidebar"
              class="btn-hamburger"
              aria-label="Toggle sidebar"
            >
              &#9776;
            </button>
            <span class="fw-bold fs-5">{{ currentMenu }}</span>
          </div>

          <!-- Kanan: Dropdown -->
          <div class="navbar-right">
            <button class="btn-settings" @click="toggleDropdown">
              <i class="bi bi-gear"></i>
            </button>

            <div v-if="dropdownOpen" class="dropdown-profile">
              <a href="#" class="dropdown-item" @click.prevent="goToProfile">
                <i class="bi bi-person-circle me-2"></i> Profile
              </a>
              <a href="#" class="dropdown-item" @click="logout">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </div>
          </div>
        </div>
      </nav>

      <!-- Router View -->
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "./api";
import { useUserStore } from "./stores/user";
import { storeToRefs } from "pinia";
import { watch } from "vue";

const router = useRouter();
const route = useRoute();
const sidebarOpen = ref(true);
const userStore = useUserStore();
const { user } = storeToRefs(userStore);

const role = ref("User");
// Navbar visibility logic
const showNavbar = computed(() => {
  return route.name !== "home" && route.name !== "auth.login";
});

// Sidebar toggle function
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};
const dropdownOpen = ref(false);

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};
const goToProfile = () => {
  dropdownOpen.value = false;
  router.push({ name: "user.profile" });
};
const logout = async () => {
  try {
    await api.post("/api/logout", {}, { withCredentials: true });

    localStorage.removeItem("token");
    localStorage.removeItem("user");
    localStorage.removeItem("userId");
    dropdownOpen.value = false;
    router.push({ name: "auth.login" });
    userStore.user = null;
    localStorage.removeItem("user");
  } catch (error) {
    console.error("Logout gagal:", error.response?.data || error.message);
    alert("Logout gagal. Silakan coba lagi.");
  }
};
const currentMenu = computed(() => {
  const nameMap = {
    "dashboard.index": "Dashboard",
    // "posts.index": "Posts",
    "user.index": "User",
    "user.create": "Tambah User",
    "user.edit": "Edit User",
    "user.detail": "Detail User",
    "user.profile": "Profile",
    "book.index": "Buku",
    "book.create": "Tambah Buku",
    "book.edit": "Edit Buku",
    "book.detail": "Detail Buku",
    "category.index": "Kategori",
    "category.create": "Tambah Kategori",
    "category.detail": "Detail Kategori",
    "member.index": "Keanggotaan",
    "member.create": "Tambah Anggota",
    "member.edit": "Edit Anggota",
    "member.detail": "Detail Anggota",
    "loan.index": "Data Peminjaman Buku",
    "loan.create": "Peminjaman Buku",
    "loan.edit": "Pengembalian Buku",
    "loan.detail": "Detail Peminjaman",
  };
  return nameMap[route.name] || "";
});

onMounted(() => {
  userStore.loadFromLocalStorage();
  userStore.fetchUserFromApi();
  const savedRole = localStorage.getItem("userRole");
  role.value = savedRole || "User";
});

watch(
  () => userStore.user,
  async (newUser) => {
    if (newUser?.id) {
      try {
        const response = await api.get(`/api/users/${newUser.id}`);
        name.value = response.data.data.name;
        email.value = response.data.data.email;
        password.value = ""; // kosongkan password biar aman
      } catch (err) {
        console.error(err);
      } finally {
        isLoading.value = false;
      }
    } else {
      isLoading.value = false;
    }
  }
);
</script>

<style scoped>
.size {
  font-size: 22px;
}
.sidebar {
  width: 250px;
  height: 100vh;
  position: fixed;
  background-color: #343a40;
  color: white;
  transition: transform 0.3s ease;
  transform: translateX(-100%);
}
.sidebar-open {
  transform: translateX(0);
}

.sidebar-header {
  display: flex;
  justify-content: start;
  align-items: center;
  height: 80px;
  padding: 15px;
}

.sidebar-nav {
  list-style-type: none;
  padding: 0;
}
.sidebar-nav li {
  padding: 10px 15px;
}
.sidebar-nav a {
  color: white;
  text-decoration: none;
}
.main-content {
  margin-left: 250px;
  transition: margin-left 0.3s ease;
  margin-left: 0;
}
.theme-toggle {
  margin: 10px;
}
.dropdown-profile {
  position: absolute;
  top: 40px;
  right: 0;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 6px;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
  min-width: 160px;
  z-index: 1000;
}

.dropdown-item {
  display: block;
  padding: 10px 15px;
  color: #333;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f1f1f1;
}

.sidebar-open + .main-content {
  margin-left: 250px;
}
.btn-hamburger {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: inherit;
  cursor: pointer;
}
.navbar-right {
  position: relative;
}
.logo {
  display: inline-block;
  vertical-align: middle;
}

.logo-container {
  width: 50px;
  height: 50px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.orbit {
  position: absolute;
  border-radius: 50%;
  border: 2px solid transparent;
  box-sizing: border-box;
}

.orbit-1 {
  width: 100%;
  height: 100%;
  border-top-color: #4f46e5;
  border-right-color: #6366f1;
  border-bottom-color: #818cf8;
  border-left-color: #a5b4fc;
  animation: spin 12s linear infinite;
}

.orbit-2 {
  width: 70%;
  height: 70%;
  border-top-color: #6366f1;
  border-right-color: #818cf8;
  border-bottom-color: #a5b4fc;
  border-left-color: #c7d2fe;
  animation: spin-reverse 8s linear infinite;
}

.digital-core {
  width: 40%;
  height: 40%;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-radius: 30%;
  transform: rotate(45deg);
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 0 20px rgba(79, 70, 229, 0.5);
  position: relative;
  overflow: hidden;
}

.digital-core::before {
  content: "";
  position: absolute;
  width: 150%;
  height: 2px;
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(45deg);
  animation: scan 4s linear infinite;
}

.pixel-grid {
  position: absolute;
  width: 80%;
  height: 80%;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-template-rows: repeat(4, 1fr);
  gap: 2px;
  transform: rotate(-45deg);
}

.pixel {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
  transition: all 0.3s ease;
}

.pixel:hover {
  background: rgba(255, 255, 255, 0.5);
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes spin-reverse {
  0% {
    transform: rotate(360deg);
  }
  100% {
    transform: rotate(0deg);
  }
}

@keyframes scan {
  0% {
    top: -50%;
  }
  100% {
    top: 150%;
  }
}
</style>
