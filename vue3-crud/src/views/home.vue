<template>
  <div
    id="app"
    class="min-h-screen flex flex-col items-center justify-center p-4 bg-gradient"
  >
    <!-- Logo -->
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

    <!-- Konten Utama -->
    <div class="text-center max-w-2xl bg-opacity-90 p-6 rounded-xl shadow-2xl">
      <!-- Slogan -->
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
        Selamat Datang di <span class="text-indigo-600">Digital Space</span>
      </h1>

      <!-- Deskripsi -->
      <p class="text-lg text-white mb-8 leading-relaxed">
        Buka Pintu Dunia Ajaib, Pinjam Buku dan Mulai Petualanganmu!
      </p>

      <router-link
        :to="{ name: 'auth.login' }"
        class="action-btn btn btn-md bg-indigo-600 shadow hover:bg-indigo-700 text-white font-medium py-3 px-8 rounded-full text-lg"
      >
        Mulai Jelajahi Dunia
      </router-link>
    </div>
  </div>
</template>

<style>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");
body {
  font-family: "Poppins", sans-serif;
}
.logo-container {
  transition: transform 0.3s ease;
  width: 300px; /* Sesuaikan ukuran jika perlu */
  height: 300px; /* Sesuaikan ukuran jika perlu */
}
.logo-container:hover {
  transform: scale(1.05);
}
.action-btn {
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}
.bg-gradient {
  background: linear-gradient(to right, #1e3a8a, #2dd4bf);
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
