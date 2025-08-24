<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router"; // ← Tambahkan ini!
import api from "../../api";
import Swal from "sweetalert2";

const posts = ref([]);
const isLoading = ref(true);
const router = useRouter(); // ← Inisialisasi router

// Ambil data post
const fetchDataPosts = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/posts");
    posts.value = response.data.data.data;
  } catch (error) {
    console.error("Gagal fetch data:", error);
  } finally {
    isLoading.value = false;
  }
};

// Cek login saat komponen dimuat
onMounted(() => {
  const token = localStorage.getItem("token");

  if (!token) {
    router.push({ name: "auth.login" }); // pastikan ini sesuai nama route login-mu
  } else {
    fetchDataPosts();
  }
});

// Hapus post
const deletePost = async (id) => {
  try {
    await api.delete(`/api/posts/${id}`);
    fetchDataPosts();
  } catch (error) {
    console.error("Gagal hapus post:", error);
  }
};
const confirmDelete = (id) => {
  Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data tidak bisa dikembalikan setelah dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      deletePost(id);
      Swal.fire("Terhapus!", "Data berhasil dihapus.", "success");
    }
  });
};
</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row">
      <div class="col-md-12">
        <router-link
          :to="{ name: 'posts.create' }"
          class="btn btn-md btn-success rounded shadow border-0 mb-3"
          >ADD NEW POST</router-link
        >
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <table class="table table-bordered">
              <thead class="bg-dark text-white">
                <tr>
                  <th scope="col">Image</th>
                  <th scope="col">Title</th>
                  <th scope="col">Content</th>
                  <th scope="col" style="width: 15%">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Loading -->
                <tr v-if="isLoading">
                  <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="posts.length == 0">
                  <td colspan="5" class="text-center">
                    <div class="alert alert-danger mb-0">
                      Data Belum Tersedia!
                    </div>
                  </td>
                </tr>
                <tr v-else v-for="(post, index) in posts" :key="index">
                  <td class="text-center">
                    <img :src="post.image" width="200" class="rounded-3" />
                  </td>
                  <td>{{ post.title }}</td>
                  <td>{{ post.content }}</td>
                  <td class="text-center">
                    <router-link
                      :to="{ name: 'posts.edit', params: { id: post.id } }"
                      class="btn btn-sm btn-primary rounded-sm shadow border-0 me-2"
                      title="Edit"
                      ><i class="bi bi-pencil"></i
                    ></router-link>
                    <!-- <router-link
                      :to="{ name: 'user.detail', params: { id: user.id } }"
                      class="btn btn-sm btn-warning rounded-sm shadow border-0 me-2"
                      title="Detail"
                      ><i class="bi bi-info-lg"></i></router-link
                    > -->
                    <button
                      @click.prevent="confirmDelete(posts.id)"
                      class="btn btn-sm btn-danger rounded-sm shadow border-0"
                      title="Delete"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
