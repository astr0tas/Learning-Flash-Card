document.addEventListener('alpine:init', () =>
{
  Alpine.data('trashData', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    records,
    RECORD_TYPE_BAG,
    RECORD_TYPE_CARD,
    filteredRecords: [...records],
    unSortedFilteredRecords: [...records],
    selectCard: '',
    selectedBags: [],
    selectedCards: [],
    sort: {
      column: null,
      direction: null,
    },
    COLUMNS: {
      NAME_OR_TITLE: 'name',
      TYPE: 'type',
      DELETED_AT: 'deleted_at',
    },
    DIRECTIONS: {
      ASC: 'asc',
      DESC: 'desc',
    },
    openCardDetailModal(content)
    {
      this.$dispatch('set-selected-card-data', content);
      document.getElementById('card_detail_modal').setAttribute('open',true);
    },
    closeCardDetailModal()
    {
      this.$dispatch('set-selected-card-data', '');
      document.getElementById('card_detail_modal').removeAttribute('open');
    },
    filterRecords(search)
    {
      if (!search)
      {
        this.filteredRecords = [...this.records];
        this.unSortedFilteredRecords = [...this.filteredRecords];
        return;
      }

      const normalizedSearch = this.removeDiacritics(search.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredRecords = this.records.filter(record =>
      {
        if (record.type === this.RECORD_TYPE_BAG)
        {
          const normalizedBagName = this.removeDiacritics(record.name.toLowerCase());
          return searchKeywords.some(keyword => normalizedBagName.includes(keyword));
        } else if (record.type === this.RECORD_TYPE_CARD) {
          const normalizedCardTitle = this.removeDiacritics(record.title.toLowerCase());
          return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
        }
      });

      this.unSortedFilteredRecords = [...this.filteredRecords];

      this.checkAllRecords();
    },
    checkAllRecords() {
      this.$refs.check_all_records.checked = this.filteredRecords.length && this.filteredRecords.every(record =>
      {
        if (record.type === this.RECORD_TYPE_BAG) {
          return this.selectedBags.some(id => id == record.id);
        } else if (record.type === this.RECORD_TYPE_CARD) {
          return this.selectedCards.some(id => id == record.id);
        }
      });
    },
    toggleAllRecords(event)
    {
      const value = event.target.checked;

      const filteredBags = this.filteredRecords.filter(r => r.type === this.RECORD_TYPE_BAG);
      const filteredCards = this.filteredRecords.filter(r => r.type === this.RECORD_TYPE_CARD);

      if (value) {
        this.selectedBags = filteredBags.map(r => r.id);
        this.selectedCards = filteredCards.map(r => r.id);
      } else {
        this.selectedBags = this.selectedBags.filter(id => !filteredBags.some(r => r.id == id));
        this.selectedCards = this.selectedCards.filter(id => !filteredCards.some(r => r.id == id));
      }
    },
    setSort(column, direction)
    {
      this.sort.column = column;
      this.sort.direction = direction;

      // 1. Use the spread operator for a shallow copy (Massive speed boost)
      this.filteredRecords = [...this.unSortedFilteredRecords];

      if (direction === null)
      {
        return;
      }

      // 2. Determine a multiplier to completely eliminate the nested switch statements
      const modifier = direction === this.DIRECTIONS.DESC ? -1 : 1;

      this.filteredRecords.sort((a, b) => {
        let result = 0;

        switch (column) {
          case this.COLUMNS.NAME_OR_TITLE:
            const strA = a.title || a.name || '';
            const strB = b.title || b.name || '';
            result = strA.localeCompare(strB);
            break;

          case this.COLUMNS.TYPE:
            result = a.type - b.type;
            break;

          case this.COLUMNS.DELETED_AT:
            // 3. Native Date parsing is significantly faster than dayjs
            const timeA = a.deletedAt ? Date.parse(a.deletedAt) : 0;
            const timeB = b.deletedAt ? Date.parse(b.deletedAt) : 0;
            result = timeA - timeB;
            break;
        }

        // Apply the direction modifier (Ascending = * 1, Descending = * -1)
        return result * modifier;
      });
    },
    init()
    {
      this.$watch('selectedBags', () => {
        this.$dispatch('update-select-bag', this.selectedBags);
        this.checkAllRecords();
      });
      this.$watch('selectedCards', () => {
        this.$dispatch('update-select-card', this.selectedCards);
        this.checkAllRecords();
      });
    }
  }));
});