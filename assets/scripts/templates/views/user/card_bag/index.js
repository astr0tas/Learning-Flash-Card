document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    bagList,
    cardList,
    selectedBags: [],
    selectedCards: [],
    resetNewBagModal()
    {
      document.getElementById('new_bag_name').dispatchEvent(new Event('reset'));
      document.getElementById('new_bag_description').dispatchEvent(new Event('reset'));
    },
    resetNewCardModal()
    {
      document.getElementById('title').dispatchEvent(new Event('reset'));
      document.getElementById('sub_title').dispatchEvent(new Event('reset'));
      document.getElementById('description').dispatchEvent(new Event('reset'));
      document.getElementById('card_type').dispatchEvent(new Event('reset'));
      document.getElementById('card_color').dispatchEvent(new Event('reset'));
      document.getElementById('card_text_color').dispatchEvent(new Event('reset'));
    },
    moveObject()
    {

    },
    deleteObject()
    {
      
    },
  }));
});