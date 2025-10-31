/**
 * Get all Fridays in the current month
 * @returns {Array<Date>} Array of Date objects representing Fridays
 */
export function getFridaysOfMonth() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    
    const fridays = [];
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Find the first Friday
    let date = new Date(firstDay);
    while (date.getDay() !== 5) { // 5 is Friday
        date.setDate(date.getDate() + 1);
    }
    
    // Collect all Fridays
    while (date <= lastDay) {
        fridays.push(new Date(date));
        date.setDate(date.getDate() + 7);
    }
    
    return fridays;
}

/**
 * Format a date as "Month Day, Year"
 * @param {Date} date 
 * @returns {string}
 */
export function formatDate(date) {
    return date.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric', 
        year: 'numeric' 
    });
}

/**
 * Format a date as "Month Day"
 * @param {Date} date 
 * @returns {string}
 */
export function formatShortDate(date) {
    return date.toLocaleDateString('en-US', { 
        month: 'long', 
        day: 'numeric'
    });
}
