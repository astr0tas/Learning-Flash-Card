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
    }
  }));
});