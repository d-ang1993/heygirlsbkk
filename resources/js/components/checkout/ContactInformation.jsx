/** @jsxImportSource react */
import React, { useMemo, useState, useEffect } from "react";

// Common country codes with flags (emoji)
const COUNTRY_CODES = [
  { code: "+1", country: "US/CA", flag: "🇺🇸", name: "United States/Canada" },
  { code: "+44", country: "UK", flag: "🇬🇧", name: "United Kingdom" },
  { code: "+66", country: "TH", flag: "🇹🇭", name: "Thailand" },
  { code: "+65", country: "SG", flag: "🇸🇬", name: "Singapore" },
  { code: "+60", country: "MY", flag: "🇲🇾", name: "Malaysia" },
  { code: "+62", country: "ID", flag: "🇮🇩", name: "Indonesia" },
  { code: "+61", country: "AU", flag: "🇦🇺", name: "Australia" },
  { code: "+64", country: "NZ", flag: "🇳🇿", name: "New Zealand" },
  { code: "+81", country: "JP", flag: "🇯🇵", name: "Japan" },
  { code: "+82", country: "KR", flag: "🇰🇷", name: "South Korea" },
  { code: "+86", country: "CN", flag: "🇨🇳", name: "China" },
  { code: "+91", country: "IN", flag: "🇮🇳", name: "India" },
  { code: "+33", country: "FR", flag: "🇫🇷", name: "France" },
  { code: "+49", country: "DE", flag: "🇩🇪", name: "Germany" },
  { code: "+34", country: "ES", flag: "🇪🇸", name: "Spain" },
  { code: "+39", country: "IT", flag: "🇮🇹", name: "Italy" },
];

export default function ContactInformation({
  formData,
  onInputChange,
  isExpanded,
  onToggle,
}) {
  // Check if contact section is complete (check raw phone number without dashes)
  const isComplete = useMemo(() => {
    const rawPhone = formData.billing_phone?.replace(/\D/g, "") || "";
    return !!(
      formData.billing_email?.trim() && 
      rawPhone.length >= 10 // At least 10 digits
    );
  }, [formData.billing_email, formData.billing_phone]);

  // Parse phone number to extract country code and number
  const parsePhone = (phone) => {
    if (!phone) return { countryCode: "+66", number: "" };
    
    // Check if phone starts with a country code
    const matched = COUNTRY_CODES.find(cc => phone.startsWith(cc.code));
    if (matched) {
      // Extract number part and format it
      const rawNumber = phone.substring(matched.code.length).trim().replace(/\D/g, "");
      return {
        countryCode: matched.code,
        number: formatPhoneNumber(rawNumber), // Format with dashes for display
      };
    }
    
    // Default to Thailand if no code found
    const rawNumber = phone.replace(/\D/g, "");
    return { countryCode: "+66", number: formatPhoneNumber(rawNumber) };
  };
  
  // Format phone number with dashes (XXX-XXX-XXXX)
  const formatPhoneNumber = (value) => {
    // Remove all non-digits
    const number = typeof value === 'string' ? value.replace(/\D/g, "") : String(value || "").replace(/\D/g, "");
    
    // Format: XXX-XXX-XXXX
    if (number.length <= 3) {
      return number;
    } else if (number.length <= 6) {
      return `${number.slice(0, 3)}-${number.slice(3)}`;
    } else {
      return `${number.slice(0, 3)}-${number.slice(3, 6)}-${number.slice(6, 10)}`;
    }
  };

  const [phoneData, setPhoneData] = useState(() => 
    parsePhone(formData.billing_phone)
  );
  
  // Track dropdown open state
  const [dropdownOpen, setDropdownOpen] = useState(false);
  
  // Track focus state for floating labels
  const [emailFocused, setEmailFocused] = useState(false);
  const [phoneFocused, setPhoneFocused] = useState(false);

  // Get current country flag and details
  const currentCountry = COUNTRY_CODES.find(cc => cc.code === phoneData.countryCode) || COUNTRY_CODES[2]; // Default to Thailand

  // Update phoneData when formData.billing_phone changes externally
  useEffect(() => {
    setPhoneData(parsePhone(formData.billing_phone));
  }, [formData.billing_phone]);

  const handleCountryCodeChange = (countryCode) => {
    const newCode = countryCode;
    const fullPhone = newCode + " " + phoneData.number;
    setPhoneData({ countryCode: newCode, number: phoneData.number });
    
    // Update formData
    const syntheticEvent = {
      target: {
        name: "billing_phone",
        value: fullPhone.trim(),
      },
    };
    onInputChange(syntheticEvent);
  };

  const handlePhoneNumberChange = (e) => {
    const inputValue = e.target.value;
    const rawNumber = inputValue.replace(/\D/g, ""); // Remove all non-digits for storage
    const formattedNumber = formatPhoneNumber(rawNumber); // Format with dashes for display
    const fullPhone = phoneData.countryCode + " " + rawNumber;
    
    // Store formatted number for display, but send raw to formData
    setPhoneData({ ...phoneData, number: formattedNumber });
    
    // Update formData with raw number (no dashes) - this is what gets submitted
    const syntheticEvent = {
      target: {
        name: "billing_phone",
        value: fullPhone.trim(),
      },
    };
    onInputChange(syntheticEvent);
  };

  return (
    <div className="border-b border-gray-200 pb-4 sm:pb-6">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between text-left py-2 -mx-2 px-2 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div className="flex-1 min-w-0 pr-2">
          <h2 className="text-sm sm:text-base font-semibold text-gray-900">
            Contact Information
          </h2>
          {!isExpanded && isComplete && (
            <div className="mt-1 text-xs sm:text-sm text-gray-600 truncate">
              {formData.billing_email}
            </div>
          )}
        </div>
        <div className="ml-2 sm:ml-4 flex items-center gap-2 sm:gap-3 flex-shrink-0">
          {isComplete && (
            <>
              <svg
                className="h-4 w-4 sm:h-5 sm:w-5 text-green-600 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <span className="text-xs sm:text-sm text-gray-600 hidden sm:inline">Edit</span>
            </>
          )}
          <svg
            className={`h-4 w-4 sm:h-5 sm:w-5 text-gray-500 transition-transform flex-shrink-0 ${
              isExpanded ? "rotate-180" : ""
            }`}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </div>
      </button>

      {isExpanded && (
        <div className="mt-3 sm:mt-4 space-y-3 sm:space-y-4">
          {/* Email Field with Floating Label */}
          <div className="relative">
            <div className="relative">
              <div className="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                <svg
                  className="h-4 w-4 sm:h-5 sm:w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <input
                id="billing_email"
                name="billing_email"
                type="email"
                autoComplete="email"
                value={formData.billing_email}
                onChange={onInputChange}
                onFocus={() => setEmailFocused(true)}
                onBlur={() => setEmailFocused(false)}
                className="block w-full rounded-lg bg-white border border-gray-300 pl-10 sm:pl-11 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
                placeholder=" "
              />
              <label
                htmlFor="billing_email"
                className={`absolute left-10 sm:left-11 pointer-events-none transition-all duration-200 ${
                  formData.billing_email || emailFocused
                    ? "top-1.5 sm:top-2 text-xs text-gray-700"
                    : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                }`}
              >
                Email address{" "}
                <abbr className="required text-red-600 ml-0.5" title="required">
                  *
                </abbr>
              </label>
            </div>
          </div>

          {/* Phone Field with Combined Country Code + Number */}
          <div className="relative">
            {/* Combined Phone Input Container */}
            <div className="relative flex items-center rounded-lg bg-white border border-gray-300 focus-within:border-gray-900 focus-within:ring-2 focus-within:ring-gray-900/10 transition-all pt-5 sm:pt-6 pb-2">
              {/* Country Code Selector */}
              <div className="relative">
                <button
                  type="button"
                  onClick={(e) => {
                    e.preventDefault();
                    setDropdownOpen(!dropdownOpen);
                  }}
                  className="flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-2 text-xs sm:text-sm text-gray-900 hover:bg-gray-50 focus:outline-none transition-colors rounded-l-lg touch-manipulation"
                >
                  <span className="text-base sm:text-lg leading-none">{currentCountry.flag}</span>
                  <span className="font-medium">{phoneData.countryCode}</span>
                  <svg
                    className={`h-3 w-3 sm:h-4 sm:w-4 text-gray-400 transition-transform ${
                      dropdownOpen ? "rotate-180" : ""
                    }`}
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </button>
                
                {/* Dropdown Menu */}
                {dropdownOpen && (
                  <>
                    <div
                      className="fixed inset-0 z-10"
                      onClick={() => setDropdownOpen(false)}
                    />
                    <div className="absolute top-full left-0 mt-1 w-[calc(100vw-2rem)] sm:w-56 max-w-[280px] max-h-64 overflow-y-auto rounded-lg bg-white border border-gray-200 shadow-lg z-20">
                      <div className="py-1">
                        {COUNTRY_CODES.map((cc) => (
                          <button
                            key={cc.code}
                            type="button"
                            onClick={(e) => {
                              e.preventDefault();
                              handleCountryCodeChange(cc.code);
                              setDropdownOpen(false);
                            }}
                            className={`w-full flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-2 text-sm text-left hover:bg-gray-50 transition-colors touch-manipulation ${
                              phoneData.countryCode === cc.code
                                ? "bg-gray-50 font-medium"
                                : ""
                            }`}
                          >
                            <span className="text-base sm:text-lg leading-none">{cc.flag}</span>
                            <span className="flex-1 font-medium">{cc.code}</span>
                            <span className="text-xs text-gray-500 hidden sm:inline">{cc.country}</span>
                          </button>
                        ))}
                      </div>
                    </div>
                  </>
                )}
              </div>
              
              {/* Divider */}
              <div className="h-6 w-px bg-gray-300" />
              
              {/* Phone Number Input */}
              <div className="relative flex-1 min-w-0">
                <label
                  htmlFor="billing_phone_number"
                  className={`absolute left-2 sm:left-3 pointer-events-none transition-all duration-200 ${
                    (phoneData.number && phoneData.number.trim().length > 0) || phoneFocused
                      ? "bottom-5 sm:bottom-6 text-xs text-gray-700"
                      : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                  }`}
                >
                  Phone{" "}
                  <abbr className="required text-red-600 ml-0.5" title="required">
                    *
                  </abbr>
                </label>
                <input
                  id="billing_phone_number"
                  type="tel"
                  autoComplete="tel"
                  value={phoneData.number || ""}
                  onChange={handlePhoneNumberChange}
                  onFocus={() => setPhoneFocused(true)}
                  onBlur={() => setPhoneFocused(false)}
                  placeholder=" "
                  className="block w-full bg-transparent border-0 pl-2 sm:pl-3 pr-2 sm:pr-4 py-0 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:outline-none"
                />
              </div>
            </div>
            
            {/* Hidden input to store full phone number for form submission (raw number without dashes) */}
            <input
              type="hidden"
              id="billing_phone"
              name="billing_phone"
              value={(phoneData.countryCode + " " + phoneData.number.replace(/\D/g, "")).trim()}
            />
          </div>
        </div>
      )}
    </div>
  );
}

