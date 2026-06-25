/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    theme: {
      extend: {
        colors: {
          dtube: {
            primary:   "#223154",
            accent:    "#F01A30",
            dark:      "#262626",
            "bg":      "#f5f5f5",
            "bg-card": "#ffffff",
            "text":    "#212121",
            "muted":   "#777777",
            "night":   "#111111",
            "night-card": "#1d1d1d",
            "night-text": "#F0F0F0",
          }
        },
        fontFamily: {
          dtube: ["Roboto", "system-ui", "sans-serif"],
        }
      },
    },
    plugins: [],
  }
