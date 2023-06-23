<template>
  <div>
    <div class="row justify-content-center">
      <h1>Upload Multiple images with Progress @ cwiser.com</h1>
      <div class="form-group col-md-6">
        <label class="form-label">Title</label>
        <input
          type="text"
          class="form-control"
          id="title"
          v-model="title"
          value=""
          placeholder="Enter your title"
        />
      </div>
      <div class="form-group col-md-6">
        <label class="form-label">Description</label>
        <input
          type="text"
          class="form-control"
          id="description"
          v-model="description"
          value=""
          placeholder="Enter your description"
        />
      </div>
    </div>
    <input type="file" accept="image/*" multiple @change="handleFileUpload" />
    <button @click="uploadImages">Upload</button>

    <div>
      <p>Name: {{ title }}</p>
      <p>Description: {{ description }}</p>
    </div>

    <div v-for="(image, index) in uploadedImages" :key="index">
      <img :src="image.url" alt="Uploaded Image" height="200px" width="200px" />
      <div>{{ image.progress }}%</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      title: "",
      description: "",
      files: [],
      uploadedImages: [],
    };
  },
  methods: {
    handleFileUpload(e) {
      this.title = $("#title").val();
      this.description = $("#description").val();
      this.files = e.target.files;
    },
    uploadImages() {
      for (let i = 0; i < this.files.length; i++) {
        const file = this.files[i];
        const formData = new FormData();
        formData.append("image", file);

        const image = {
          url: URL.createObjectURL(file),
          progress: 0,
        };

        this.uploadedImages.push(image);

        this.uploadImage(formData, image, i);
      }
    },
    uploadImage(formData, image, index) {
      const config = {
        headers: {
          "Content-Type": "multipart/form-data",
        },
        onUploadProgress: (progressEvent) => {
            console.log(progressEvent);
          const progress = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
          image.progress = progress;
        },
      };
    },
  },
};
</script>
