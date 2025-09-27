/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,php,js}",       
    "./Learner/**/*.{html,php,js}", 
  ],
  theme: {
    extend: {
      fontFamily: {
        heading:['Bungee','cursive'],
        body: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

