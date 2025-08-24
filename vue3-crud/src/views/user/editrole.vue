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
// hapus name, email, password
// tambah state role
const role = ref("");

// ambil data user, hanya ambil role
const fetchDataUsers = async () => {
  isLoading.value = true;
  try {
    const response = await api.get(`/api/users/${route.params.id}`);
    role.value = response.data.data.role;
  } catch (error) {
    console.error("Gagal ambil data user:", error);
  } finally {
    isLoading.value = false;
  }
};

// di updateUser kirim data role saja
const updateUser = async () => {
  let formData = new FormData();

  formData.append("role", role.value);
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

onMounted(() => {
  const token = localStorage.getItem("token");

  if (!token) {
    router.push({ name: "auth.login" });
    return;
  }

  // langsung panggil fetch data tanpa cek role
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
              <!-- Role -->
              <div class="mb-3">
                <label class="form-label fw-bold">Role</label>
                <select v-model="role" class="form-select">
                  <option value="User">User</option>
                  <option value="Administrator">Administrator</option>
                  <!-- tambah role lain kalau ada -->
                </select>
                <div v-if="errors.role" class="alert alert-danger mt-2">
                  <span>{{ errors.role[0] }}</span>
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-md btn-primary rounded-sm shadow border-0"
              >
                Update Role
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
