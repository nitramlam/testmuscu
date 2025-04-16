
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#f0f9ff",
          100: "#e0f2fe",
          200: "#bae6fd",
          300: "#7dd3fc",
          400: "#38bdf8",
          500: "#0ea5e9",
          600: "#0284c7",
          700: "#0369a1",
          800: "#075985",
          900: "#0c4a6e",
        },
        secondary: {
          50: "#f5f3ff",
          100: "#ede9fe",
          200: "#ddd6fe",
          300: "#c4b5fd",
          400: "#a78bfa",
          500: "#8b5cf6",
          600: "#7c3aed",
          700: "#6d28d9",
          800: "#5b21b6",
          900: "#4c1d95",
        },
        header: {
          bg: "#dc2626",
          text: "#ffffff",
          hover: "#93c5fd",
        },
        danger: {
          500: "#ef4444",
          600: "#dc2626",
        },
        success: {
          500: "#10b981",
          600: "#059669",
        },
      },
      fontFamily: {
        sans: ["Inter", "system-ui", "sans-serif"],
      },
      animation: {
        "fade-in": "fadeIn 0.5s ease-in-out",
        "fade-in-down": "fadeInDown 0.5s ease-out", 
        "fade-in-up": "fadeInUp 0.5s ease-out",
        float: "float 6s ease-in-out infinite",
        "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
        "bounce-slow": "bounce 2s infinite"
      },
      boxShadow: {
        nav: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
        card: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
        "card-hover": "0 10px 15px -3px rgba(0, 0, 0, 0.1)",
        glow: "0 0 15px rgba(59, 130, 246, 0.3)",
        soft: "0 10px 30px -15px rgba(0, 0, 0, 0.1)",
        "soft-hover": "0 15px 40px -15px rgba(0, 0, 0, 0.15)",
        "inner-xl": "inset 0 4px 8px 0 rgba(0, 0, 0, 0.1)"
      },
      gradientColorStops: {
        "primary-gradient": ["#38bdf8", "#0ea5e9"],
        "secondary-gradient": ["#818cf8", "#6366f1"],
        "danger-gradient": ["#ef4444", "#dc2626"],
        "success-gradient": ["#10b981", "#059669"]
      },
      keyframes: {
        fadeIn: {
          from: { opacity: 0, transform: "translateY(10px)" },
          to: { opacity: 1, transform: "translateY(0)" }
        },
        fadeInDown: {
          from: { opacity: 0, transform: "translateY(-20px)" },
          to: { opacity: 1, transform: "translateY(0)" }
        },
        fadeInUp: {
          from: { opacity: 0, transform: "translateY(20px)" },
          to: { opacity: 1, transform: "translateY(0)" }
        },
        float: {
          "0%, 100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-10px)" }
        }
      }
    }
  },
  variants: {
    extend: {
      scale: ["hover", "active"],
      transform: ["hover", "active"],
      boxShadow: ["hover", "active"],
      opacity: ["disabled"],
    }
  }
};