<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();

const books = ref([]);
const members = ref([]);

const bookId = ref("");
const memberId = ref("");
const borrowedAt = ref("");

const errors = ref({});

const fetchBooks = async () => {
  try {
    const res = await api.get("/api/books");
    books.value = res.data;
  } catch (error) {
    console.error("Gagal fetch books:", error);
  }
};

const fetchMembers = async () => {
  try {
    const res = await api.get("/api/members");
    members.value = res.data;
  } catch (error) {
    console.error("Gagal fetch members:", error);
  }
};

onMounted(() => {
  fetchBooks();
  fetchMembers();
});

const storeLoan = async () => {
  errors.value = {};

  try {
    await api.post("/api/loans", {
      book_id: bookId.value,
      member_id: memberId.value,
      borrowed_at: borrowedAt.value || null,
    });
    Swal.fire("Success", "Peminjaman berhasil dibuat!", "success");
    router.push({ path: "/loan/index" });
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      Swal.fire("Error", "Gagal menyimpan data peminjaman.", "error");
    }
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow rounded border-0">
          <div class="card-body">
            <form @submit.prevent="storeLoan">
              <!-- Dropdown Member -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="member">Nama Anggota</label>
                <select
                  id="member"
                  class="form-select"
                  v-model="memberId"
                >
                  <option value="" disabled>Pilih anggota...</option>
                  <option v-for="member in members" :key="member.id" :value="member.id">
                    {{ member.name }}
                  </option>
                </select>
                <div v-if="errors.member_id" class="text-danger mt-1">{{ errors.member_id[0] }}</div>
              </div>

              <!-- Dropdown Buku -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="book">Pilih Buku</label>
                <select
                  id="book"
                  class="form-select"
                  v-model="bookId"
                >
                  <option value="" disabled>Pilih buku...</option>
                  <option v-for="book in books" :key="book.id" :value="book.id">
                    {{ book.title }}
                  </option>
                </select>
                <div v-if="errors.book_id" class="text-danger mt-1">{{ errors.book_id[0] }}</div>
              </div>

              <!-- Tanggal Peminjaman -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="borrowed_at">Tanggal Peminjaman</label>
                <input
                  id="borrowed_at"
                  type="date"
                  class="form-control"
                  v-model="borrowedAt"
                  style="width: 20%;"
                />
                <div v-if="errors.borrowed_at" class="text-danger mt-1">{{ errors.borrowed_at[0] }}</div>
              </div>

              <button type="submit" class="btn btn-primary rounded-sm shadow border-0">
                Simpan Peminjaman
              </button>
              <button
                  class="btn btn-secondary rounded-sm shadow border-0 ml-2"
                  @click="router.push({ path: '/loan/index' })"
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
