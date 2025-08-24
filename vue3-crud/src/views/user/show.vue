<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const name = ref("");
const email = ref("");
const isLoading = ref(true); // loading state

onMounted(async () => {
  try {
    const response = await api.get(`/api/users/${route.params.id}`);
    name.value = response.data.data.name;
    email.value = response.data.data.email;
  } catch (error) {
    console.error("Gagal ambil data user", error);
  } finally {
    isLoading.value = false; // stop loading setelah selesai
  }
});

const fetchDataUsers = async () => {
  isLoading.value = true;
  try {
    const response = await api.get(`/api/users/${route.params.id}`);
    name.value = response.data.data.name;
    email.value = response.data.data.email;
  } catch (error) {
    console.error("Gagal ambil data user:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  const role = localStorage.getItem("userRole") || "User";

  if (!token) {
    router.push({ name: "auth.login" });
    return;
  }
  if (role !== "Administrator") {
    Swal.fire({
      icon: "error",
      title: "Akses Ditolak",
      text: "Halaman ini hanya untuk Administrator!",
      confirmButtonText: "Kembali"
    }).then(() => {
      router.push({ name: "dashboard.index" });
    });
    return;
  }

  console.log("Akses Administrator, lanjut fetch...");
  fetchDataUsers();
});
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <h4 class="fw-bold mb-3">Detail User</h4>

            <!-- Loading -->
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <!-- Data User -->
            <div v-else>
              <!-- Name -->
              <div class="mb-3">
                <label class="form-label fw-bold">Name</label>
                <p class="form-control-plaintext">{{ name }}</p>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <p class="form-control-plaintext">{{ email }}</p>
              </div>
              <router-link
                :to="{ name: 'user.edit', params: { id: route.params.id } }"
                class="btn  btn-primary border-0"
                title="Edit"
              >
                <i class="bi bi-pencil"></i> Edit
              </router-link>
              <button
                class="btn btn-secondary ml-2"
                @click="router.push({ path: '/users/index' })"
              >
                Kembali
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
