import { defineStore } from 'pinia';
// import axios from '../../plugins/axios';

export const useRecipesStore = defineStore('recipes', {
  state: () => ({
    recipes: [],
  }),
  getters: {
    allRecipes: (state) => state.recipes,
    recipeById: (state) => (id) => state.recipes.find((recipe) => recipe.id === id),
  },
  actions: {
    async fetchRecipes() {
        const response = await axios.get('/recipes');
        this.recipes = response.data;
    },
    async createRecipe(recipe) {
        const response = await axios.post('/recipes', recipe);
        this.recipes.push(response.data);
    },
    async updateRecipe(updatedRecipe) {
        const response = await axios.put(`/recipes/${updatedRecipe.id}`, updatedRecipe);
        const index = this.recipes.findIndex(r => r.id === updatedRecipe.id);
        if (index !== -1) {
            this.recipes[index] = response.data;
        }
    },
    async deleteRecipe(id) {
        await axios.delete(`/recipes/${id}`);
        this.recipes = this.recipes.filter(r => r.id !== id);
    },
  },
});