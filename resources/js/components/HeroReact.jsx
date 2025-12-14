/** @jsxImportSource react */
import React, { useEffect } from "react";

export default function HeroReact({
  heading = "NEW ARRIVAL",
  subheading = "",
  ctaText = "Shop Now",
  ctaUrl = "/shop",
  alignment = "center",
  images = [],
}) {
  // Get first image for background
  const firstImage = Array.isArray(images) && images.length > 0 ? images[0] : null;

  // Load and console log all images from WordPress
  useEffect(() => {
    console.log("🔵 HeroReact: Loading images from WordPress");
    console.log("📦 Images array:", images);
    console.log("📊 Total images:", images.length);
    
    if (Array.isArray(images) && images.length > 0) {
      console.log("✅ Images found! Logging each image:");
      images.forEach((image, index) => {
        console.log(`  Image ${index + 1}:`, {
          url: image.url,
          alt: image.alt,
          link: image.link,
          full: image,
        });
      });
      console.log("🖼️ First image (used as background):", firstImage);
    } else {
      console.log("⚠️ No images provided or images array is empty");
    }
  }, [images, firstImage]);

  return (
    <section className="hero-react hero-react--fullscreen">
      {/* Background Image */}
      {firstImage && firstImage.url && (
        <div 
          className="hero-react__bg"
          style={{ backgroundImage: `url(${firstImage.url})` }}
        />
      )}
      <div className="container text-container">
          {heading && (
            <div className="hero-react__heading">
              <h1 className="hero-react__title">{heading}</h1>
            </div>
          )}
          
          {subheading && (
            <div className="hero-react__subheading">
              <p className="hero-react__subtitle">{subheading}</p>
            </div>
          )}

          {ctaText && (
            <div className="hero-react__cta-wrapper">
              <a href={ctaUrl} className="btn btn-primary hero-react__cta">
                {ctaText}
              </a>
            </div>
          )}
      </div>
    </section>
  );
}
