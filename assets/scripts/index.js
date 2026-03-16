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
    ...dropdownProcessor,
  }));
});

// This variable contains every dropdowns and the closing logic for them
const dropdownProcessor = {
  dropdowns: {
    appMenuDropdown: false,
    addDropdown: false,
    optionDropdown: false,
  },
  checkAppClick: function (event)
  {
    // Check if menu exists AND click was outside
    const menu = this.$refs.appMenuDropdownRef;
    if (menu && !menu.contains(event.target)) {
        this.dropdowns.appMenuDropdown = false;
    }

    // Check if add dropdown exists AND click was outside
    const addDropdownElem = this.$refs.addDropdownRef;
    if (addDropdownElem && !addDropdownElem.contains(event.target)) {
        this.dropdowns.addDropdown = false;
    }

    // Check if option dropdown exists AND click was outside
    const optionDropdownElem = this.$refs.optionDropdownRef;
    if (optionDropdownElem && !optionDropdownElem.contains(event.target)) {
        this.dropdowns.optionDropdown = false;
    }
  }
};