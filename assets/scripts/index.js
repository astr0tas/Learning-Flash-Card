import { dropdownProcessor } from './dropdown.js';

// This is the main JavaScript entry point for the application
// This should include any global JavaScript needed across the app
document.addEventListener('alpine:init', () =>
{
  Alpine.data('appData', () => ({
    isLoading: false,
    darkMode: (function ()
    {
      // Get the dark_mode cookie
      const darkModeCookie = document.cookie.split('; ').find(row => row.startsWith('dark_mode='));
      if (darkModeCookie) {
        const darkModeValue = darkModeCookie.split('=')[1];
        return Boolean(eval(darkModeValue));
      }
      return false;
    })(),
    setDarkMode(value)
    {
      this.darkMode = value;
      // Set the dark_mode cookie to expire in 5 years
      const d = new Date();
      d.setTime(d.getTime() + (365*24*60*60*1000*5));
      const expires = "expires="+ d.toUTCString();
      document.cookie = "dark_mode=" + value + ";" + expires + ";path=/";
    },
    ...dropdownProcessor,
    removeDiacritics(str)
    {
      return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    },
    getContrastColor(hexColor) {
      // Fallback if no color is provided
      if (!hexColor) return '#000000';

      // Strip the hash
      let hex = hexColor.replace('#', '');

      // Handle 3-character shorthand hexes (e.g., #fff -> ffffff)
      if (hex.length === 3) {
        hex = hex.split('').map(char => char + char).join('');
      }

      // Convert to RGB
      const r = parseInt(hex.substr(0, 2), 16);
      const g = parseInt(hex.substr(2, 2), 16);
      const b = parseInt(hex.substr(4, 2), 16);

      // YIQ Brightness Formula
      const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;

      // 128 is the midway point of brightness
      return (yiq >= 128) ? '#000000' : '#FFFFFF';
    }
  }));
});