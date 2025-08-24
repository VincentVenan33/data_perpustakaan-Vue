// stores/user.js
import { defineStore } from "pinia";
import axios from "axios";

export const useUserStore = defineStore("user", {
  state: () => ({
    user: null
  }),
  actions: {
    setUser(userData) {
      this.user = userData;
      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("userId", userData.id); // simpan userId juga
    },
    loadFromLocalStorage() {
      const data = localStorage.getItem("user");
      console.log('loadFromLocalStorage called, data:', data);
      if (data) {
        this.user = JSON.parse(data);
      }
    },
    async fetchUserFromApi() {
      const userId = localStorage.getItem("userId");
      if (!userId) return;

      try {
        const res = await axios.get(`/api/users/${userId}`);
        this.user = res.data;
      } catch (err) {
        console.error("Gagal ambil data user:", err);
      }
    }
  }
});
