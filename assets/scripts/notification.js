export const notificationProcessor = {
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
  }
};