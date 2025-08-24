// import vue router
import { createRouter, createWebHistory } from "vue-router";

// define routes
const routes = [
  {
    path: "/",
    name: "home",
    component: () => import(/* webpackChunkName: "home" */ "../views/home.vue"),
    meta: { title: "DIGITAL SPACE" }
  },
  {
    path: "/auth/login",
    name: "auth.login",
    component: () =>
      import(/* webpackChunkName: "login" */ "../views/auth/login.vue"),
    meta: { title: "DIGITAL SPACE | LOGIN" }
  },
  {
    path: "/dashboard",
    name: "dashboard.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/dashboard/index.vue"),
    meta: { title: "DIGITAL SPACE | DASHBOARD" }
  },
  {
    path: "/users/index",
    name: "user.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/user/index.vue"),
    meta: { title: "DIGITAL SPACE | USER" }
  },
  {
    path: "/users/create",
    name: "user.create",
    component: () =>
      import(/* webpackChunkName: "create" */ "../views/user/create.vue"),
    meta: {
      title: "DIGITAL SPACE | CREATE USER"
    }
  },
  {
    path: "/users/edit/:id",
    name: "user.edit",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/user/edit.vue"),
    meta: { title: "DIGITAL SPACE | EDIT USER" }
  },
  {
    path: "/users/editrole/:id",
    name: "user.editrole",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/user/editrole.vue"),
    meta: { title: "DIGITAL SPACE | EDIT USER ROLE" }
  },
  {
    path: "/users/detail/:id",
    name: "user.detail",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/user/show.vue"),
    meta: { title: "DIGITAL SPACE | DETAIL USER" }
  },
  {
    path: "/users/profile",
    name: "user.profile",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/user/profile.vue"),
    meta: { title: "DIGITAL SPACE | Profile" }
  },
  {
    path: "/book/index",
    name: "book.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/book/index.vue"),
    meta: { title: "DIGITAL SPACE | BUKU" }
  },
  {
    path: "/book/create",
    name: "book.create",
    component: () =>
      import(/* webpackChunkName: "create" */ "../views/book/create.vue"),
    meta: { title: "DIGITAL SPACE | TAMBAH BUKU" }
  },
  {
    path: "/book/:id/edit",
    name: "book.edit",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/book/edit.vue"),
    meta: { title: "DIGITAL SPACE | EDIT BUKU" }
  },
  {
    path: "/book/detail/:id",
    name: "book.detail",
    component: () =>
      import(/* webpackChunkName: "show" */ "../views/book/show.vue"),
    meta: { title: "DIGITAL SPACE | DETAIL Book" }
  },
  {
    path: "/category/index",
    name: "category.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/category/index.vue"),
    meta: { title: "DIGITAL SPACE | KATEGORI" }
  },
  {
    path: "/category/create",
    name: "category.create",
    component: () =>
      import(/* webpackChunkName: "create" */ "../views/category/create.vue"),
    meta: { title: "DIGITAL SPACE | TAMBAH KATEGORI" }
  },
  {
    path: "/category/detail/:id",
    name: "category.detail",
    component: () =>
      import(/* webpackChunkName: "show" */ "../views/category/show.vue"),
    meta: { title: "DIGITAL SPACE | DETAIL Kategori" }
  },
  {
    path: "/loan/index",
    name: "loan.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/loan/index.vue"),
    meta: { title: "DIGITAL SPACE | LOAN" }
  },

  {
    path: "/loan/create",
    name: "loan.create",
    component: () =>
      import(/* webpackChunkName: "create" */ "../views/loan/create.vue"),
    meta: { title: "DIGITAL SPACE | TAMBAH LOAN" }
  },
  {
    path: "/loan/detail/:id",
    name: "loan.detail",
    component: () =>
      import(/* webpackChunkName: "show" */ "../views/loan/show.vue"),
    meta: { title: "DIGITAL SPACE | DETAIL PEMINJAMAN BUKU" }
  },
  {
    path: "/member/index",
    name: "member.index",
    component: () =>
      import(/* webpackChunkName: "index" */ "../views/member/index.vue"),
    meta: { title: "DIGITAL SPACE | ANGGOTA" }
  },
  {
    path: "/member/create",
    name: "member.create",
    component: () =>
      import(/* webpackChunkName: "create" */ "../views/member/create.vue"),
    meta: { title: "DIGITAL SPACE |TAMBAH ANGGOTA" }
  },
  {
    path: "/member/detail/:id",
    name: "member.detail",
    component: () =>
      import(/* webpackChunkName: "show" */ "../views/member/show.vue"),
    meta: { title: "DIGITAL SPACE | DETAIL Anggota" }
  },
  {
    path: "/member/:id/edit",
    name: "member.edit",
    component: () =>
      import(/* webpackChunkName: "edit" */ "../views/member/edit.vue"),
    meta: { title: "DIGITAL SPACE | EDIT ANGGOTA" }
  },
  // {
  //   path: "/posts/index",
  //   name: "posts.index",
  //   component: () =>
  //     import(/* webpackChunkName: "index" */ "../views/posts/index.vue"),
  //   meta: { title: "DIGITAL SPACE | POSTS" }
  // },
  // {
  //   path: "/posts/create",
  //   name: "posts.create",
  //   component: () =>
  //     import(/* webpackChunkName: "create" */ "../views/posts/create.vue"),
  //   meta: { title: "DIGITAL SPACE | CREATE POST" }
  // },
  // {
  //   path: "/posts/edit/:id",
  //   name: "posts.edit",
  //   component: () =>
  //     import(/* webpackChunkName: "edit" */ "../views/posts/edit.vue"),
  //   meta: { title: "DIGITAL SPACE | EDIT POST" }
  // }
];

// create router
const router = createRouter({
  history: createWebHistory(),
  routes
});

// guard: redirect ke login jika belum login dan bukan home/login
router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem("token");

  // Ubah judul tab jika ada meta.title
  if (to.meta && to.meta.title) {
    document.title = to.meta.title;
  }

  // izinkan home dan login tanpa login
  if (to.name === "home" || to.name === "auth.login") {
    return next();
  }

  // jika belum login, redirect ke login
  if (!isLoggedIn) {
    return next({ name: "auth.login" });
  }

  // jika sudah login, lanjutkan
  next();
});

export default router;
