<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";
import { useUserStore } from "../../stores/user";

const router = useRouter();
const userStore = useUserStore();
const name = ref("");
const email = ref("");
const password = ref("");
const errors = ref([]);
const isLoading = ref(true);

onMounted(async () => {
  if (!userStore.user) {
    userStore.loadFromLocalStorage();
  }
  if (userStore.user?.id) {
    try {
      const res = await api.get(`/api/users/${userStore.user.id}`);
      name.value = res.data.data.name;
      email.value = res.data.data.email;
      password.value = res.data.data.password;
    } finally {
      isLoading.value = false;
    }
  } else {
    isLoading.value = false;
  }
});

const updateProfile = async () => {
  let formData = new FormData();
  formData.append("name", name.value);
  formData.append("email", email.value);
  if (password.value && password.value.trim() !== "") {
    formData.append("password", password.value);
  }
  formData.append("_method", "PATCH");

  try {
    const userId = userStore.user?.id;
    if (!userId) throw new Error("User ID tidak ditemukan");

    await api.post(`/api/users/${userId}`, formData);

    await Swal.fire({
      icon: "success",
      title: "Berhasil!",
      text: "Profil berhasil diperbarui.",
      confirmButtonText: "OK"
    });

  } catch (err) {
    const validationErrors = err.response?.data?.errors || {};
    errors.value = validationErrors;

    Swal.fire({
      icon: "error",
      title: "Gagal memperbarui profil",
      html: Object.values(validationErrors)
        .flat()
        .map(msg => `<p>${msg}</p>`)
        .join(""),
      confirmButtonText: "OK"
    });
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card shadow rounded">
          <div class="card-body">

            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <form v-else @submit.prevent="updateProfile">
              <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="name"
                  placeholder="Masukkan nama"
                />
                <div v-if="errors.name" class="text-danger mt-1">
                  {{ errors.name[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input
                  type="email"
                  class="form-control"
                  v-model="email"
                  placeholder="Masukkan email"
                />
                <div v-if="errors.email" class="text-danger mt-1">
                  {{ errors.email[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input
                  type="password"
                  class="form-control"
                  v-model="password"
                  placeholder="Masukkan password bila ingin merubah"
                />
                <div v-if="errors.password" class="text-danger mt-1">
                  {{ errors.password[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary">
                Simpan Perubahan
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
