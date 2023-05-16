/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    fontFamily: {
        'display': ['Comfortaa'],
        'title': ['Rajdhani']
    },
    extend: {
        colors: {
            'primary': 'rgb(126 34 206)',
            'primary-light': 'rgb(147 51 234)',
            'primary-superlight': 'rgb(216 180 254)'
        },
    },
  },
  plugins: [],
}
