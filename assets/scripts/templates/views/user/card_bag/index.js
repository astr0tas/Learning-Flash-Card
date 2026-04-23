document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    bagList,
    filteredBagList: JSON.parse(JSON.stringify(bagList)),
    cardList,
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    viewCardList,
    viewFilteredCardList: JSON.parse(JSON.stringify(viewCardList)),
    selectCard: '',
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
    openCardDetailModal(index)
    {
      this.selectCard = this.viewCardList[index];
      this.$refs.card_detail_modal.open = true;
    },
    closeCardDetailModal()
    {
      this.selectCard = '';
      this.$refs.card_detail_modal.open = false;
    },
    openEditCardModal(index)
    {

    },
    filterBagAndCard(search)
    {
      if (!search)
      {
        this.filteredBagList = JSON.parse(JSON.stringify(this.bagList));
        this.filteredCardList = JSON.parse(JSON.stringify(this.cardList));
        this.viewFilteredCardList = JSON.parse(JSON.stringify(this.viewCardList));
        return;
      }

      const normalizedSearch = this.removeDiacritics(search.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredBagList = this.bagList.filter(bag => {
        const normalizedBagName = this.removeDiacritics(bag.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedBagName.includes(keyword));
      });

      this.filteredCardList = this.cardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });

      this.viewFilteredCardList = this.viewCardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });
    },
  }));
});