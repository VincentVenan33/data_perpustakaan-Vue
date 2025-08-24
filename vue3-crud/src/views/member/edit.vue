<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const name = ref("");
const email = ref("");
const joinedAt = ref(""); // pakai format yyyy-mm-dd (input type date)
const isActive = ref(false);
const errors = ref({});
const isLoading = ref(true);
const preferences = ref({
  instagram: "",
  facebook: "",
  phone: ""
});

onMounted(async () => {
  try {
    const response = await api.get(`/api/members/${route.params.id}`);
    const data = response.data.data;

    name.value = data.name || "";
    email.value = data.email || "";
    joinedAt.value = data.joined_at ? data.joined_at.substring(0, 10) : "";
    isActive.value = data.is_active || false;

    if (data.preferences) {
      // Kalau preferences masih string JSON, parsing dulu
      try {
        preferences.value = JSON.parse(data.preferences);
      } catch {
        // Kalau gagal parse, beri default kosong
        preferences.value = { instagram: "", facebook: "", phone: "" };
      }
    } else {
      // Jika tidak ada, beri default kosong
      preferences.value = { instagram: "", facebook: "", phone: "" };
    }
  } catch (error) {
    // error handling tetap sama
  } finally {
    isLoading.value = false;
  }
});

const updateMember = async () => {
  errors.value = {}; // reset errors

  try {
    await api.patch(`/api/members/${route.params.id}`, {
      name: name.value,
      email: email.value,
      joined_at: joinedAt.value,
      is_active: isActive.value,
      preferences: JSON.stringify(preferences.value) // stringify karena backend validasi json string
    });

    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Data member berhasil diperbarui."
    });

    router.push({ path: "/member/index" });
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      Swal.fire({
        icon: "error",
        title: "Gagal update",
        text: "Terjadi kesalahan saat menyimpan data."
      });
    }
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <form v-else @submit.prevent="updateMember">
              <div class="mb-3">
                <label for="name" class="form-label fw-bold">Nama</label>
                <input
                  type="text"
                  id="name"
                  class="form-control"
                  v-model="name"
                  placeholder="Masukkan nama"
                />
                <div v-if="errors.name" class="text-danger mt-1">
                  {{ errors.name[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label fw-bold">Email</label>
                <input
                  type="email"
                  id="email"
                  class="form-control"
                  v-model="email"
                  placeholder="Masukkan email"
                />
                <div v-if="errors.email" class="text-danger mt-1">
                  {{ errors.email[0] }}
                </div>
              </div>

              <div class="mb-3">
                <label for="joined_at" class="form-label fw-bold"
                  >Tanggal Bergabung</label
                >
                <input
                  type="date"
                  id="joined_at"
                  class="form-control"
                  v-model="joinedAt"
                  style="width: 20%"
                />
                <div v-if="errors.joined_at" class="text-danger mt-1">
                  {{ errors.joined_at[0] }}
                </div>
              </div>

              <!-- Preferences: Instagram -->
              <div class="mb-3">
                <label class="form-label fw-bold">Instagram</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.instagram"
                  placeholder="Username Instagram"
                />
                <div
                  v-if="errors['preferences.instagram']"
                  class="text-danger mt-1"
                >
                  {{ errors["preferences.instagram"][0] }}
                </div>
              </div>

              <!-- Preferences: Facebook -->
              <div class="mb-3">
                <label class="form-label fw-bold">Facebook</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.facebook"
                  placeholder="Username Facebook"
                />
                <div
                  v-if="errors['preferences.facebook']"
                  class="text-danger mt-1"
                >
                  {{ errors["preferences.facebook"][0] }}
                </div>
              </div>

              <!-- Preferences: No HP -->
              <div class="mb-3">
                <label class="form-label fw-bold">No HP</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.phone"
                  placeholder="Nomor Handphone"
                />
                <div
                  v-if="errors['preferences.phone']"
                  class="text-danger mt-1"
                >
                  {{ errors["preferences.phone"][0] }}
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold" for="is_active">
                  Status Aktif
                </label>
                <div class="form-check form-switch mb-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="is_active"
                    v-model="isActive"
                  />
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-md btn-primary rounded-sm shadow border-0"
              >
                Update
              </button>
              <button
                class="btn btn-secondary rounded-sm shadow border-0 ml-2"
                @click="router.push({ path: '/member/index' })"
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

<style scoped>
/* Perbesar toggle switch */
.form-check-input {
  width: 50px; /* Lebar switch */
  height: 24px; /* Tinggi switch */
  cursor: pointer;
}

/* Buat bulatan switch lebih besar */
.form-check-input:checked {
  background-color: #0d6efd; /* warna aktif */
  border-color: #0d6efd;
}

/* Sesuaikan posisi lingkaran bulatan toggle */
.form-check-input:focus {
  box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 0.25);
}

.form-check-input::before {
  width: 14px;
  height: 14px;
  top: 0;
  left: 0;
  border-radius: 17px;
  background-color: white;
  transition: transform 0.3s ease-in-out;
}

/* Pindahkan bulatan ke kanan saat checked */
.form-check-input:checked::before {
  transform: translateX(26px);
}
</style>
