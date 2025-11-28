/**
 * Helper function to extract numeric value from WooCommerce price HTML
 * @param {string|number} priceHtml - The price value which may be HTML-formatted
 * @returns {number} - The numeric value as a float
 */
export const extractPriceNumber = (priceHtml) => {
  if (!priceHtml) return 0;
  
  // Convert to string if it's not already
  const priceString = String(priceHtml);
  
  // If it's already a plain number string, return it
  if (/^\d+\.?\d*$/.test(priceString.trim())) {
    return parseFloat(priceString.trim());
  }
  
  // Remove all HTML tags and extract number
  // Handle both regular HTML and escaped HTML (with \")
  const cleaned = priceString
    .replace(/\\"/g, '"') // Unescape quotes
    .replace(/<[^>]*>/g, '') // Remove HTML tags
    .replace(/&#\d+;/g, '') // Remove HTML entities like &#3647;
    .replace(/[^\d.]/g, ''); // Remove everything except digits and decimal point
  
  // Extract the number (first match of digits with optional decimal)
  const numberMatch = cleaned.match(/(\d+\.?\d*)/);
  if (numberMatch) {
    return parseFloat(numberMatch[1]);
  }
  
  return 0;
};
