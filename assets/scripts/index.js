import { dropdownProcessor } from './dropdown.js';

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
    },
    pushNotification(message, type)
    {
      let style = {
        color: 'white',
        borderRadius: '5px',
        display: 'flex',
        alignItems: 'center',
      };
      let avatar = '';

      switch(type){
        case 'info':
          style.background = "#2196f3";
          avatar = '/svg/info_toast.svg';
          break;
        case 'warning':
          style.background = "#ffc107";
          avatar = '/svg/warning_toast.svg';
          break;
        case 'error':
          style.background = "#ff5252";
          avatar = '/svg/error_toast.svg';
          break;
        case 'success':
          style.background = '#4caf50';
          avatar = '/svg/success_toast.svg';
          break;
      }

      const toast = Toastify({
        text: `<p class='max-w-[350px]'>${message}</p>
        <img class="toast-close" src="/svg/close_toast.svg">`,
        duration: 10000, // Display toast for 10 seconds
        // newWindow: false,
        // offset: {
        //   x: 0, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
        //   y: 0, // vertical axis - can be a number or a string indicating unity. eg: '2em'
        // },
        escapeMarkup: false,
        close: false, // Display close icon
        gravity: "top", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style,
        avatar,
        onClick: function(){} // Callback after click
      });

      toast.showToast();

      toast.toastElement.querySelector('.toast-close').addEventListener('click', () => {
        toast.removeElement(toast.toastElement);
      });
    },
    init()
    {
      this.$watch('darkMode', (value) => {
        // If value is true, remove the dark_theme class from the body tag and add light_theme class to it, else do the opposite
        if (value) {
          document.body.classList.remove('light_theme');
          document.body.classList.add('dark_theme');
        } else {
          document.body.classList.remove('dark_theme');
          document.body.classList.add('light_theme');
        }
      });
    }
  }));
});