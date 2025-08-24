<template>
  <div class="login-page">
    <div class="logo-container mb-8">
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
      <h2 class="text-indigo-600 font-bold text-4xl">Digital Space</h2>
    <div class="login-card">
      <div class="login-card-header">
        <router-link
          :to="{ name: 'home' }"
          style="margin-right: 10px; color: white"
          title="Kembali ke Beranda "
        >
          <i class="fas fa-home"></i>
        </router-link>

        <h2 class="login-card-title">Login</h2>
      </div>
      <form @submit.prevent="login">
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input
            type="email"
            id="email"
            placeholder="Masukkan email"
            v-model="email"
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input
            type="password"
            id="password"
            placeholder="Masukkan password"
            v-model="password"
          />
        </div>

        <div v-if="error" style="color: red; margin-bottom: 10px">
          {{ error }}
        </div>

        <button
          type="submit"
          class="action-btn btn btn-md bg-indigo-600 shadow hover:bg-indigo-700 text-white font-medium py-3 px-8 rounded-full text-lg mt-4"
        >
          Masuk
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import { useUserStore } from "../../stores/user";
const userStore = useUserStore();

const router = useRouter();

const email = ref("");
const password = ref("");
const error = ref("");

// fungsi login
const login = async () => {
  try {
    const response = await api.post("/api/login", {
      email: email.value,
      password: password.value
    });
    const userData = response.data.user;

    localStorage.setItem("token", response.data.token);
    localStorage.setItem("token", response.data.token);
    localStorage.setItem("userId", response.data.user.id);
    localStorage.setItem("userName", response.data.user.name);
    localStorage.setItem("userEmail", response.data.user.email);
    localStorage.setItem("userRole", response.data.user.role);
    localStorage.setItem("user", JSON.stringify(response.data.user));
    userStore.user = userData;
    userStore.setUser(userData);
    userStore.loadFromLocalStorage();

    router.push({ path: "/dashboard" });
  } catch (err) {
    error.value = err.response?.data?.message || "Gagal login. Coba lagi.";
  }
};
</script>

<style scoped>
.login-page {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 90vh;
  gap: 2rem;
  padding: 1rem;
}

.login-card {
  background: transparent;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 5px 10px 10px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

/* .login-card h2 {
  margin-bottom: 20px;
} */

.login-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.login-card-title {
  color: #ffffff;
  flex-grow: 1;
  text-align: center;
  margin: 0;
}

.home-icon {
  font-size: 20px;
  text-decoration: none;
  color: white;
}

.form-group {
  text-align: left;
  margin-bottom: 15px;
}

.form-label {
  color: #ffffff;
}

.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 5px;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.btn-login {
  background-color: #2d6cdf;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  width: 100%;
  font-weight: bold;
  margin-top: 10px;
}

.btn-login:hover {
  background-color: #244ea1;
}
.logo-container {
  width: 200px;
  height: 200px;
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
