<script setup>
// import
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

// init router & route
const router = useRouter();
const route = useRoute();

// state
const name = ref("");
const email = ref("");
const password = ref("");
const errors = ref([]);
const isLoading = ref(true); // loading state

// ambil data user saat komponen dimuat
onMounted(async () => {
  try {
    const response = await api.get(`/api/users/${route.params.id}`);
    name.value = response.data.data.name;
    email.value = response.data.data.email;
    password.value = response.data.data.passsword;
  } catch (error) {
    console.error("Gagal ambil data user:", error);
  } finally {
    isLoading.value = false; // stop loading
  }
});

// method update user
const updateUser = async () => {
  let formData = new FormData();

  formData.append("name", name.value);
  formData.append("email", email.value);
  if (password.value && password.value.trim() !== "") {
    formData.append("password", password.value);
  }
  formData.append("_method", "PATCH");

  try {
    await api.post(`/api/users/${route.params.id}`, formData);
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "User berhasil diperbarui",
      timer: 2000,
      showConfirmButton: false
    });
    router.push({ path: "/users/index" });
  } catch (error) {
    errors.value = error.response?.data?.errors || {};
    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: "Update user gagal. Cek kembali data yang diinput."
    });
  }
};

const fetchDataUsers = async () => {
  isLoading.value = true;
  try {
    const response = await api.get(`/api/users/${route.params.id}`);
    name.value = response.data.data.name;
    email.value = response.data.data.email;
    password.value = response.data.data.password;
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
            <!-- Loading Spinner -->
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <!-- Form Edit -->
            <form v-else @submit.prevent="updateUser()">
              <!-- Name -->
              <div class="mb-3">
                <label class="form-label fw-bold">Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="name"
                  placeholder="Name"
                />
                <div v-if="errors.name" class="alert alert-danger mt-2">
                  <span>{{ errors.name[0] }}</span>
                </div>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input
                  type="email"
                  class="form-control"
                  v-model="email"
                  placeholder="Email"
                />
                <div v-if="errors.email" class="alert alert-danger mt-2">
                  <span>{{ errors.email[0] }}</span>
                </div>
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input
                  type="password"
                  class="form-control"
                  v-model="password"
                  placeholder="Masukkan password bila ingin merubah"
                />
                <div v-if="errors.password" class="alert alert-danger mt-2">
                  <span>{{ errors.password[0] }}</span>
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-md btn-primary rounded-sm shadow border-0"
              >
                Update User
              </button>
              <button
                class="btn btn-secondary ml-2"
                @click="router.push({ path: '/users/index' })"
              >
                Kembali
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
