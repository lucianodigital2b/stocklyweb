<template>
    <div class="colored-bg">

    </div>
    <div class="col-lg-7 mx-auto my-7">
        <card class="rounded-4 p-md-3 shadow-sm">
            <h3 class="mb-5">{{ form.id ? 'Edit Recipe' : 'Create a recipe' }}</h3>
            <form @submit.prevent="saveRecipe">
                <div class="row mb-7">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label>Title</label>
                            <input v-model="form.title" :class="{ 'is-invalid': form.errors.has('title') }" class="form-control" name="title">
                            <has-error :form="form" field="title" />
            
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            
                            <textarea rows="5" v-model="form.description" :class="{ 'is-invalid': form.errors.has('description') }" class="form-control" name="description"></textarea>
                            <has-error :form="form" field="description" />
            
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label>Thumbnail (optional)</label>
                        <div class="thumbnail-wrapper position-relative">
                            <button class="file-input-button " @click.prevent="onPickFile">
                                <div v-if="!thumbnailPreview" class="input-empty"></div>
                                <div
                                    v-if="thumbnailPreview"
                                    class="image-preview"
                                    :style="{ backgroundImage: `url(${thumbnailPreview})` }"
                                >
                                </div>
                                
                            </button>
                            <button class="btn btn-light clear-thumbnail p-1 py-0" @click.prevent="clearThumbnail" v-if="thumbnailPreview"><XMarkIcon class="hero-icon"></XMarkIcon></button>

                        </div>

                        <div class="text-muted">
                            <small>
                                Use JPEG or PNG. Must be at least 960 x 960. Max file size: 30MB
                            </small>
                        </div>
                        <input
                            type="file"
                            style="display: none"
                            ref="thumbnail"
                            accept=".png, .jpg, .jpeg"
                            @change="handleFile"
                        />
                        <HasError :form="form" field="thumbnail" />
                    </div>
                </div>
                <div class="row">
                    <h6>Gallery</h6>
                    <div class="col-md-12">


                        <div v-bind="getRootProps()" class="gallery-zone">
                            <input v-bind="getInputProps()" />
                            <div v-if="isDragActive">Drop the files here ...</div>
                            <div v-else>Drag 'n' drop some files here, or click to select files</div>
                        </div>

                        <div class="recipe-gallery">
                            <div class="recipe-gallery-item position-relative" v-for="(image, idx) in form.gallery" :key="image">
                                <img :src="getImage(image)" alt="">
                                <button class="position-absolute btn-light btn rounded-3 d-flex align-items-center gap-1 justify-content-center rounded-pill p-1 top-0 right-0" @click.prevent="removeGalleryImage(idx)"><XMarkIcon class="hero-icon"></XMarkIcon></button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6 class="mb-6">Ingredients</h6>
                            <div class="text-muted mb-3">
                                List each ingredient on a separate line, specifying the quantity (e.g., cups, tablespoons) and any necessary preparation (e.g., sifted, softened, chopped)
                            </div>
                            <div class="d-flex gap-2 align-items-center mb-3" v-for="ingredient in form.ingredients" :key="ingredient">    
                                <input v-model="ingredient.name" class="form-control " :placeholder="ingredient.placeholder ?? ''">
                                <v-button type="light" class="rounded-pill p-1" @click.prevent="removeIngredient"><XMarkIcon class="hero-icon"></XMarkIcon></v-button>
                            </div>

                            <v-button type="outline-primary" @click.prevent="addIngredient" class="px-7"><PlusIcon class="hero-icon"></PlusIcon> add ingredient</v-button>
                            <has-error :form="form" field="ingredients" />
                            
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6 class="mb-6">Directions</h6>
                            <div class="text-muted mb-3">
                                Provide step-by-step instructions for making your recipe, including details like oven temperatures, cooking or baking times, and pan sizes. Use optional headers to organize sections of the process, such as "Prep," "Bake," or "Decorate."
                            </div>
                            <div v-for="(step, index) in form.steps" :key="step">
                                <div for="" class="form-label flex-1">Step {{ index +1 }}</div>
                                <div class="d-flex gap-2 align-items-center mb-3">
                                    <input v-model="step.description" class="form-control" :placeholder="step.placeholder ?? ''">
                                    <v-button type="light" @click.prevent="removeStep" class="rounded-pill p-1"><XMarkIcon class="hero-icon"></XMarkIcon></v-button>

                                </div>
                            </div>
                            <v-button type="outline-primary" @click.prevent="addStep"><PlusIcon class="hero-icon"></PlusIcon> add step</v-button>
                            <has-error :form="form" field="steps" />
            
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <label>Prep time</label>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <input placeholder="e.g. 30"  v-model="form.prep_time" :class="{ 'is-invalid': form.errors.has('prep_time') }" class="form-control" name="prep_time" type="number">
                            <has-error :form="form" field="prep_time" />
            
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="">
                            <select name="prep_time_type" id="" class="form-control" v-model="form.prep_time_type">
                                <option value="1">mins</option>    
                                <option value="2">hours</option>    
                                <option value="3">days</option>    
                            </select>
                            <has-error :form="form" field="prep_time_type" />
                        </div>
                    </div>

                    <div class="col-md-8 mb-5">
                        <label>Servings</label>
                        <input placeholder="e.g. 3"  v-model="form.servings" :class="{ 'is-invalid': form.errors.has('servings') }" class="form-control" name="servings" type="number">
                        <has-error :form="form" field="servings" />
                    </div>
                    
                </div>
                
                <v-button class="" large :loading="form.busy">save</v-button>
            </form>

        </card>
    </div>
</template>


<style scoped>

    .gallery-zone {
        padding: 1rem;
        border: 1px dashed #ffd988;
        border-radius: 0.25rem;
        text-align: center;
        cursor: pointer;
        margin-bottom: 1rem;
    }

    .recipe-gallery {
        display: flex;
        gap: 1rem;
    }

    
    .recipe-gallery-item > img {
        position: relative;
        border-radius: 10px;
        height: 100px;

    }
    .colored-bg {
        z-index: -10;
        background: #ffd988;
        padding: 6rem;
        position: absolute;
        width: 100%;
        left: 0;
        top: 61px;
    }

    .file-input-button {
        border: 1px dashed #ffd988;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        height: 150px;
        width: 100%;
        position: relative;
    }

    .input-empty {
        background: url('/img/blank-image.svg') no-repeat center center;
        background-size: cover;
        width: 150px;
        height: 100px;
    }

    .image-preview {
        width: 100%;
        height: 100px;
        background-size: cover;
        background-position: center;
    }

    .clear-thumbnail {
        position: absolute;
        top: -5px;
        right: 0;
        z-index: 5;
    }
</style>

<script setup>

import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Form from 'vform'
import { useRecipesStore } from '../../store/modules/recipes'
import { ref, reactive } from 'vue'
import axios from '../../plugins/axios';
import { PlusIcon, XMarkIcon, PencilSquareIcon } from '@heroicons/vue/24/outline'
import { useDropzone } from "vue3-dropzone";
import { get } from '@vueuse/core';

const route = useRoute();
const router = useRouter();
const store = useRecipesStore();

const form = reactive( new Form({
  title: '',
  description: '',
  thumbnail: null,
  servings: '',
  prep_time: '',
  prep_time_type: '1',
  gallery: [],
  delete_from_gallery: [],
  ingredients: [
    {
        placeholder: 'e.g. 1 spoon of sugar',
        name: '',
    }, 
    {
        name: '',
        placeholder: 'e.g. 1 cup of rice',
    }, 
    {
        name: '',
        placeholder: 'e.g. 2 spoons of cream',
    }
  ],
  steps: [
        {
            description: '',
            placeholder: 'e.g. Turn on the Airless Air Fryer',
        },
    ],

}));
const thumbnail = ref(null);
const thumbnailPreview = ref(null);

const id = route.params.id;


const saveRecipe = async () => {

    form.gallery = form.gallery.filter((image) => {
        return image.id == undefined
    });

    console.log(form.gallery);
    
    
    if (id) {
        form['_method'] = "PUT";
        const { data } = await form.post('/recipes/' + id, form)
    } else {
        await form.post('/recipes')
    }

    // router.push({ path: '/dashboard' });
};

onMounted(() => {
    if (id) {
        form.get(`/recipes/${id}`).then((response) => {
            Object.assign(form, response.data);
        });
    }
});

const addStep = () => {
    form.steps.push({});
}

const removeStep = (index) => {
    form.steps.splice(index, 1);
}

const addIngredient = () => {
    form.ingredients.push({});
}   

const removeIngredient = (index) => {
    form.ingredients.splice(index, 1);
}

const handleFile = (event) => {
    // We'll grab just the first file...
    // You can also do some client side validation here.
    const file = event.target.files[0]

    if(!file) {
        return;
    }

    if(!file.type.startsWith("image/")) {
        alert('Uplaod an image');
    }

    
    if (file.size > 31800000) { // 30MB
        alert('File too big (> 30MB)');
        return;
    }

    const reader = new FileReader();

    reader.onload = (e) => {
        thumbnailPreview.value = e.target.result;
    };

    reader.readAsDataURL(file);
    
    // Set the file object onto the form...
    form.thumbnail = file
};

const onPickFile = () => {
    thumbnail.value.click()
}
const clearThumbnail = () => {
    form.thumbnail = null
    thumbnailPreview.value = null
}


const saveFiles = (files) => {
    for (var x = 0; x < files.length; x++) {
        form.gallery.push(files[x]);
    }
};

function onDrop(acceptFiles, rejectReasons) {
    saveFiles(acceptFiles); // saveFiles as callback
}

const removeGalleryImage = (index) => {
    if(form.gallery[index].uuid)
        form.delete_from_gallery.push(form.gallery[index].uuid);

    form.gallery.splice(index, 1);

}

const options = reactive({
  onDrop,
  accept: '.jpg, .jpeg, .png',
  maxSize: 31800000
})

const getImage = (image) => {
    
    return image.original_url ? image.original_url : URL.createObjectURL(image);
}

const {isDragActive, getRootProps, getInputProps,...rest } = useDropzone(options);


</script>