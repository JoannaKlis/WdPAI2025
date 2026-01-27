const DateUtils = {
    getLocalTime: () => {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    },
    
    getLocalDate: () => {
        const now = new Date();
        return DateUtils.toISODate(now);
    },
    
    // Konwersja daty do formatu ISO (YYYY-MM-DD) dla inputów i bazy danych
    toISODate: (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },
    
    // Konwersja daty z ISO (YYYY-MM-DD) do formatu wyświetlania (DD.MM.YYYY)
    formatDisplayDate: (isoDateString) => {
        const [year, month, day] = isoDateString.split('-');
        return `${day}.${month}.${year}`;
    }
};