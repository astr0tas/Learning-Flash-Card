// This is the main JavaScript entry point for the application
// This should include any global JavaScript needed across the app
document.addEventListener('alpine:init', () =>
{
  Alpine.data('appData', () => ({
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
    appMenuExpanded: false,
    isLoading: false,
    setDarkMode(value)
    {
      this.darkMode = value;
      // Set the dark_mode cookie to expire in 5 years
      const d = new Date();
      d.setTime(d.getTime() + (365*24*60*60*1000*5));
      const expires = "expires="+ d.toUTCString();
      document.cookie = "dark_mode=" + value + ";" + expires + ";path=/";
    },
    checkAppClick: function (event)
    {
      // 1. Access refs via $refs
      const menu = this.$refs.appMenuRef;

      // 2. Check if menu exists AND click was outside
      if (menu && !menu.contains(event.target)) {
          this.appMenuExpanded = false;
      }
    }
  }));
});