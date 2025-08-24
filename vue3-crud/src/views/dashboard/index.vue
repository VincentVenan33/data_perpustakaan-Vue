<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="dashboard-container p-6 text-center bg-white bg-opacity-10 shadow-lg rounded-2xl text-white fade-zoom-in">
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
      <h2 class="text-indigo-600 font-bold text-4xl mt-4 mb-4">Digital Space</h2>
      <h2 class="text-2xl font-semibold mb-4">Selamat datang, {{ userName }}!</h2>
      <p class="text-lg mb-2">Waktu sekarang:</p>
      <div class="text-5xl font-mono">
        <span>{{ hours }}</span>
        <span :class="{ 'invisible': !showColon }">:</span>
        <span>{{ minutes }}</span>
      </div>
      <p class="text-base mt-2">{{ currentDate }}</p>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const userName = localStorage.getItem("userName") || "User";

const hours = ref("00");
const minutes = ref("00");
const currentDate = ref("");
const showColon = ref(true);

let interval = null;

const updateDateTime = () => {
  const now = new Date();

  hours.value = now.getHours().toString().padStart(2, "0");
  minutes.value = now.getMinutes().toString().padStart(2, "0");

  currentDate.value = now.toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric"
  });

  showColon.value = !showColon.value;
};

onMounted(() => {
  updateDateTime();
  interval = setInterval(updateDateTime, 1000);
});

onBeforeUnmount(() => {
  clearInterval(interval);
});
</script>

<style scoped>
.dashboard-container {
  max-width: 500px;
  backdrop-filter: blur(10px);
}

/* Fade + Zoom animation */
.fade-zoom-in {
  animation: fadeZoomIn 0.6s ease-out;
}

@keyframes fadeZoomIn {
  0% {
    opacity: 0;
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.invisible {
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
}

.logo-container {
  width: 200px;
  height: 200px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
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

